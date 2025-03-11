<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompaniaRequest;
use App\Http\Requests\UpdateCompaniaRequest;
use App\Models\Compania;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Exception;

class CompaniasController extends Controller
{


    /**
     * Mostrar el listado de compañías.
     */
    public function index()
    {
        $companias = Compania::paginate(10);
        return view('companias.index', [
            'companias' => $companias,
            'clases' => config('aseguradoras.servicios', []),
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva compañía.
     */
    public function create()
    {
        $clases = $this->getClases();
        return view('companias.create', compact('clases'));
    }

    /**
     * Almacena una nueva compañía en la base de datos.
     */
    public function store(StoreCompaniaRequest $request)
    {
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();

            $slug = Str::slug($validatedData['nombre']);
            $originalSlug = $slug;
            $counter = 1;
            while (Compania::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            $validatedData['slug'] = $slug;

            Compania::create($validatedData);
            DB::commit();

            return redirect()->route('companias.index')
                ->with('success', 'Compañía creada exitosamente.');
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear compañía: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['general' => 'Error al crear la compañía: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Mostrar los detalles de una compañía específica.
     */
    public function show(Compania $compania)
    {
        return view('companias.show', compact('compania'));
    }

    /**
     * Muestra el formulario para editar una compañía existente.
     */
    public function edit(Compania $compania)
    {
        $clases = $this->getClases();
        return view('companias.edit', compact('compania', 'clases'));
    }

    /**
     * Actualiza la información de una compañía.
     */
    public function update(UpdateCompaniaRequest $request, Compania $compania)
    {
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();
            $compania->update($validatedData);
            DB::commit();

            return redirect()->route('companias.index')
                ->with('success', 'Compañía actualizada exitosamente.');
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar compañía: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['general' => 'Error al actualizar la compañía: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Elimina una compañía de la base de datos.
     */
    public function destroy(Compania $compania)
    {
        try {
            DB::beginTransaction();

            if ($compania->seguros()->exists()) {
                throw new Exception('No se puede eliminar la compañía porque tiene seguros asociados.');
            }

            $compania->delete();
            DB::commit();

            return redirect()->route('companias.index')
                ->with('success', 'Compañía eliminada exitosamente.');
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar compañía: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['general' => 'Error al eliminar la compañía: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtiene las clases de aseguradoras desde la configuración.
     */
    private function getClases()
    {
        return array_keys(config('aseguradoras.servicios', []));
    }
}