<?php

/**
 * Created by Cristian.
 * Date: 19/09/16 11:58 PM.
 */

namespace Reliese\Coders\Model;

use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Reliese\Meta\Blueprint;
use Reliese\Meta\SchemaManager;
use Reliese\Support\Classify;

class Factory
{
    /**
     * @var \Illuminate\Database\DatabaseManager
     */
    private $db;

    /**
     * @var \Reliese\Meta\SchemaManager
     */
    protected $schemas = [];

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var \Reliese\Support\Classify
     */
    protected $class;

    /**
     * @var \Reliese\Coders\Model\Config
     */
    protected $config;

    /**
     * @var \Reliese\Coders\Model\ModelManager
     */
    protected $models;

    /**
     * @var \Reliese\Coders\Model\Mutator[]
     */
    protected $mutators = [];

    /**
     * ModelsFactory constructor.
     *
     * @param \Illuminate\Database\DatabaseManager $db
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Reliese\Support\Classify $writer
     * @param \Reliese\Coders\Model\Config $config
     */
    public function __construct(DatabaseManager $db, Filesystem $files, Classify $writer, Config $config)
    {
        $this->db = $db;
        $this->files = $files;
        $this->config = $config;
        $this->class = $writer;
    }

    /**
     * @return \Reliese\Coders\Model\Mutator
     */
    public function mutate()
    {
        return $this->mutators[] = new Mutator();
    }

    /**
     * @return \Reliese\Coders\Model\ModelManager
     */
    protected function models()
    {
        if (! isset($this->models)) {
            $this->models = new ModelManager($this);
        }

        return $this->models;
    }

    /**
     * Select connection to work with.
     *
     * @param string $connection
     *
     * @return $this
     */
    public function on($connection = null)
    {
        $this->schemas = new SchemaManager($this->db->connection($connection));

        return $this;
    }

    /**
     * @param string $schema
     */
    public function map($schema)
    {
        if (! isset($this->schemas)) {
            $this->on();
        }

        $mapper = $this->makeSchema($schema);

        foreach ($mapper->tables() as $blueprint) {
            if ($this->shouldTakeOnly($blueprint) && $this->shouldNotExclude($blueprint)) {
                $this->create($mapper->schema(), $blueprint->table());
            }
        }
    }

    /**
     * @param \Reliese\Meta\Blueprint $blueprint
     *
     * @return bool
     */
    protected function shouldNotExclude(Blueprint $blueprint)
    {
        foreach ($this->config($blueprint, 'except', []) as $pattern) {
            if (Str::is($pattern, $blueprint->table())) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param \Reliese\Meta\Blueprint $blueprint
     *
     * @return bool
     */
    protected function shouldTakeOnly(Blueprint $blueprint)
    {
        if ($patterns = $this->config($blueprint, 'only', [])) {
            foreach ($patterns as $pattern) {
                if (Str::is($pattern, $blueprint->table())) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * @param string $schema
     * @param string $table
     */
    public function create($schema, $table)
    {
        $model = $this->makeModel($schema, $table);
        $template = $this->prepareTemplate($model, 'model');

        $file = $this->fillTemplate($template, $model);

        if ($model->indentWithSpace()) {
            $file = str_replace("\t", str_repeat(' ', $model->indentWithSpace()), $file);
        }

        $this->files->put($this->modelPath($model, $model->usesBaseFiles() ? ['Base'] : []), $file);

        if ($this->needsUserFile($model)) {
            $this->createUserFile($model);
        }
    }

    /**
     * @param string $schema
     * @param string $table
     *
     * @param bool $withRelations
     *
     * @return \Reliese\Coders\Model\Model
     */
    public function makeModel($schema, $table, $withRelations = true)
    {
        return $this->models()->make($schema, $table, $this->mutators, $withRelations);
    }

    /**
     * @param string $schema
     *
     * @return \Reliese\Meta\Schema
     */
    public function makeSchema($schema)
    {
        return $this->schemas->make($schema);
    }

    /**
     * @param \Reliese\Coders\Model\Model $model
     *
     * @todo: Delegate workload to SchemaManager and ModelManager
     *
     * @return array
     */
    public function referencing(Model $model)
    {
        $references = [];

        // TODO: SchemaManager should do this
        foreach ($this->schemas as $schema) {
            $references = array_merge($references, $schema->referencing($model->getBlueprint()));
        }

        // TODO: ModelManager should do this
        foreach ($references as &$related) {
            $blueprint = $related['blueprint'];
            $related['model'] = $model->getBlueprint()->is($blueprint->schema(), $blueprint->table())
                ? $model
                : $this->makeModel($blueprint->schema(), $blueprint->table(), false);
        }

        return $references;
    }

    /**
     * @param \Reliese\Coders\Model\Model $model
     * @param string $name
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function prepareTemplate(Model $model, $name)
    {
        $defaultFile = $this->path([__DIR__, 'Templates', $name]);
        $file = $this->config($model->getBlueprint(), "*.template.$name", $defaultFile);

        return $this->files->get($file);
    }

    /**
     * @param string $template
     * @param \Reliese\Coders\Model\Model $model
     *
     * @return mixed
     */
    protected function fillTemplate($template, Model $model)
    {
        $template = str_replace('{{namespace}}', $model->getBaseNamespace(), $template);
        $template = str_replace('{{class}}', $model->getClassName(), $template);

        $properties = $this->properties($model);
        $dependencies = $this->shortenAndExtractImportableDependencies($properties, $model);
        $template = str_replace('{{properties}}', $properties, $template);

        $parentClass = $model->getParentClass();
        $dependencies = array_merge($dependencies, $this->shortenAndExtractImportableDependencies($parentClass, $model));
        $template = str_replace('{{parent}}', $parentClass, $template);

        $body = $this->body($model);
        $dependencies = array_merge($dependencies, $this->shortenAndExtractImportableDependencies($body, $model));
        $template = str_replace('{{body}}', $body, $template);

        $imports = $this->imports(array_keys($dependencies), $model);
        $template = str_replace('{{imports}}', $imports, $template);

        return $template;
    }

    /**
     * Returns imports section for model.
     *
     * @param array $dependencies Array of imported classes
     * @param Model $model
     * @return string
     */
    private function imports($dependencies, Model $model)
    {
        $imports = [];
        foreach ($dependencies as $dependencyClass) {
            // Skip when the same class
            if (trim($dependencyClass, "\\") == trim($model->getQualifiedUserClassName(), "\\")) {
                continue;
            }

            // Do not import classes from same namespace
            $inCurrentNamespacePattern = str_replace('\\', '\\\\', "/{$model->getBaseNamespace()}\\[a-zA-Z0-9_]*/");
            if (preg_match($inCurrentNamespacePattern, $dependencyClass)) {
                continue;
            }

            $imports[] = "use {$dependencyClass};";
        }

        sort($imports);

        return implode("\n", $imports);
    }

    /**
     * Extract and replace fully-qualified class names from placeholder.
     *
     * @param string $placeholder Placeholder to extract class names from. Rewrites value to content without FQN
     * @param \Reliese\Coders\Model\Model $model
     *
     * @return array Extracted FQN
     */
    private function shortenAndExtractImportableDependencies(&$placeholder, $model)
    {
        $qualifiedClassesPattern = '/([\\\\a-zA-Z0-9_]*\\\\[\\\\a-zA-Z0-9_]*)/';
        $matches = [];
        $importableDependencies = [];
        if (preg_match_all($qualifiedClassesPattern, $placeholder, $matches)) {
            foreach ($matches[1] as $usedClass) {
                $namespacePieces = explode('\\', $usedClass);
                $className = array_pop($namespacePieces);

                /**
                 * Avoid breaking same-model relationships when using base classes
                 *
                 * @see https://github.com/reliese/laravel/issues/209
                 */
                if ($model->usesBaseFiles() && $usedClass === $model->getQualifiedUserClassName()) {
                    continue;
                }

                //When same class name but different namespace, skip it.
                if (
                    $className == $model->getClassName() &&
                    trim(implode('\\', $namespacePieces), '\\') != trim($model->getNamespace(), '\\')
                ) {
                    continue;
                }

                $importableDependencies[trim($usedClass, '\\')] = true;
                $placeholder = preg_replace('!'.addslashes($usedClass).'\b!', addslashes($className), $placeholder, 1);
            }
        }

        return $importableDependencies;
    }

    /**
     * @param \Reliese\Coders\Model\Model $model
     *
     * @return string
     */
    protected function properties(Model $model)
    {
        // Process property annotations
        $annotations = '';

        foreach ($model->getProperties() as $name => $hint) {
            $annotations .= $this->class->annotation('property', "$hint \$$name");
        }

        if ($model->hasRelations()) {
            // Add separation between model properties and model relations
            $annotations .= "\n * ";
        }

        foreach ($model->getRelations() as $name => $relation) {
            // TODO: Handle collisions, perhaps rename the relation.
            if ($model->hasProperty($name)) {
                continue;
            }
            $annotations .= $this->class->annotation('property', $relation->hint()." \$$name");
        }

        return $annotations;
    }

    /**
     * @param \Reliese\Coders\Model\Model $model
     *
     * @return string
     */
    protected function body(Model $model)
    {
        $body = '';

        foreach ($model->getTraits() as $trait) {
            $body .= $this->class->mixin($trait);
        }

        $excludedConstants = [];

        if ($model->hasCustomCreatedAtField()) {
            $body .= $this->class->constant('CREATED_AT', $model->getCreatedAtField());
            $excludedConstants[] = $model->getCreatedAtField();
        }

        if ($model->hasCustomUpdatedAtField()) {
            $body .= $this->class->constant('UPDATED_AT', $model->getUpdatedAtField());
            $excludedConstants[] = $model->getUpdatedAtField();
        }

        if ($model->hasCustomDeletedAtField()) {
            $body .= $this->class->constant('DELETED_AT', $model->getDeletedAtField());
            $excludedConstants[] = $model->getDeletedAtField();
        }

        if ($model->usesPropertyConstants()) {
            // Take all properties and exclude already added constants with timestamps.
            $properties = array_keys($model->getProperties());
            $properties = array_diff($properties, $excludedConstants);

            foreach ($properties as $property) {
                $constantName = Str::upper(Str::snake($property));
                $body .= $this->class->constant($constantName, $property);
            }
        }

        $body = trim($body, "\n");
        // Separate constants from fields only if there are constants.
        if (! empty($body)) {
            $body .= "\n";
        }

        // Append connection name when required
        if ($model->shouldShowConnection()) {
            $body .= $this->class->field('connection', $model->getConnectionName());
        }

        // When table is not plural, append the table name
        if ($model->needsTableName()) {
            $body .= $this->class->field('table', $model->getTableForQuery());
        }

        if ($model->hasCustomPrimaryKey()) {
            $body .= $this->class->field('primaryKey', $model->getPrimaryKey());
        }

        if ($model->doesNotAutoincrement()) {
            $body .= $this->class->field('incrementing', false, ['visibility' => 'public']);
        }

        if ($model->hasCustomPerPage()) {
            $body .= $this->class->field('perPage', $model->getPerPage());
        }

        if (! $model->usesTimestamps()) {
            $body .= $this->class->field('timestamps', false, ['visibility' => 'public']);
        }

        if ($model->hasCustomDateFormat()) {
            $body .= $this->class->field('dateFormat', $model->getDateFormat());
        }

        if ($model->doesNotUseSnakeAttributes()) {
            $body .= $this->class->field('snakeAttributes', false, ['visibility' => 'public static']);
        }

        if ($model->usesColumnList()) {
            $properties = array_keys($model->getProperties());

            $body .= "\n";
            $body .= $this->class->field('columns', $properties);
        }

        if ($model->hasCasts()) {
            $body .= $this->class->field('casts', $model->getCasts(), ['before' => "\n"]);
        }

        if ($model->hasHidden() && ($model->doesNotUseBaseFiles() || $model->hiddenInBaseFiles())) {
            $body .= $this->class->field('hidden', $model->getHidden(), ['before' => "\n"]);
        }

        if ($model->hasFillable() && ($model->doesNotUseBaseFiles() || $model->fillableInBaseFiles())) {
            $body .= $this->class->field('fillable', $model->getFillable(), ['before' => "\n"]);
        }

        if ($model->hasHints() && $model->usesHints()) {
            $body .= $this->class->field('hints', $model->getHints(), ['before' => "\n"]);
        }

        foreach ($model->getMutations() as $mutation) {
            $body .= $this->class->method($mutation->name(), $mutation->body(), ['before' => "\n"]);
        }

        foreach ($model->getRelations() as $constraint) {
            $body .= $this->class->method(
                $constraint->name(),
                $constraint->body(),
                [
                    'before' => "\n",
                    'returnType' => $model->definesReturnTypes() ? $constraint->returnType() : null,
                ]
            );
        }

        // Make sure there not undesired line breaks
        $body = trim($body, "\n");

        return $body;
    }

    /**
     * @param \Reliese\Coders\Model\Model $model
     * @param array $custom
     *
     * @return string
     */
    protected function modelPath(Model $model, $custom = [])
    {
        $modelsDirectory = $this->path(array_merge([$this->config($model->getBlueprint(), 'path')], $custom));

        if (! $this->files->isDirectory($modelsDirectory)) {
            $this->files->makeDirectory($modelsDirectory, 0755, true);
        }

        return $this->path([$modelsDirectory, $model->getClassName().'.php']);
    }

    /**
     * @param array $pieces
     *
     * @return string
     */
    protected function path($pieces)
    {
        return implode(DIRECTORY_SEPARATOR, (array) $pieces);
    }

    /**
     * @param \Reliese\Coders\Model\Model $model
     *
     * @return bool
     */
    public function needsUserFile(Model $model)
    {
        return ! $this->files->exists($this->modelPath($model)) && $model->usesBaseFiles();
    }

    /**
     * @param \Reliese\Coders\Model\Model $model
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function createUserFile(Model $model)
    {
        $file = $this->modelPath($model);

        $template = $this->prepareTemplate($model, 'user_model');
        $template = str_replace('{{namespace}}', $model->getNamespace(), $template);
        $template = str_replace('{{class}}', $model->getClassName(), $template);
        $template = str_replace('{{imports}}', $this->formatBaseClasses($model), $template);
        $template = str_replace('{{parent}}', $this->getBaseClassName($model), $template);
        $template = str_replace('{{body}}', $this->userFileBody($model), $template);

        $this->files->put($file, $template);
    }

    /**
     * @param Model $model
     * @return string
     */
    private function formatBaseClasses(Model $model)
    {
        return "use {$model->getBaseNamespace()}\\{$model->getClassName()} as {$this->getBaseClassName($model)};";
    }

    /**
     * @param Model $model
     * @return string
     */
    private function getBaseClassName(Model $model)
    {
        return 'Base'.$model->getClassName();
    }

    /**
     * @param \Reliese\Coders\Model\Model $model
     *
     * @return string
     */
    protected function userFileBody(Model $model)
    {
        $body = '';

        if ($model->hasHidden() && !$model->hiddenInBaseFiles()) {
            $body .= $this->class->field('hidden', $model->getHidden());
        }

        if ($model->hasFillable() && !$model->fillableInBaseFiles()) {
            $body .= $this->class->field('fillable', $model->getFillable(), ['before' => "\n"]);
        }

        // Make sure there is not an undesired line break at the end of the class body
        $body = ltrim(rtrim($body, "\n"), "\n");

        return $body;
    }

    /**
     * @param \Reliese\Meta\Blueprint|null $blueprint
     * @param string $key
     * @param mixed $default
     *
     * @return mixed|\Reliese\Coders\Model\Config
     */
    public function config(?Blueprint $blueprint = null, $key = null, $default = null)
    {
        if (is_null($blueprint)) {
            return $this->config;
        }

        return $this->config->get($blueprint, $key, $default);
    }
}
