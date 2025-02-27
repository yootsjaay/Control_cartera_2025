<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compania;
use App\Http\Requests\StoreCompaniaRequest; // Nuevo FormRequest
use App\Http\Requests\UpdateCompaniaRequest; // Nuevo FormRequest
use Illuminate\Support\Facades\DB;
use Exception;
use App\Services\QualitasSeguroService;
use App\Services\HdiSegurosService;
use App\Services\GmxSeguroService;
use App\Services\BanorteSeguroService;
use Illuminate\Support\Str;

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
            'clases' => config('aseguradoras.servicios', []), // Valor por defecto si no existe
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva compañía.
     */
 
        public function create()
        {
            $clases = array_keys(config('aseguradoras.servicios', [])); // Solo las claves (nombres)
            return view('companias.create', compact('clases'));
        }
    

    /**
     * Almacena una nueva compañía en la base de datos.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'clase' => 'required|string|max:255',
        ], [
            'nombre.required' => 'El nombre de la compañía es obligatorio.',
            'clase.required' => 'Debe seleccionar una clase de servicio.',
        ]);
    
        try {
            DB::beginTransaction();
            Compania::create($validatedData);
            DB::commit();
        
            return redirect()->route('companias.index')
                ->with('success', 'Compañía creada exitosamente.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['general' => 'Error al crear la compañía: ' . $e->getMessage()])
                ->withInput();
        }

        // Generar el slug automáticamente basado en el nombre
        $slug = Str::slug($validatedData['nombre']); // Convierte "Mi Compañía" a "mi-compania"
        
        // Verificar unicidad del slug y ajustarlo si es necesario
        $originalSlug = $slug;
        $counter = 1;
        while (Compania::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        // Agregar el slug a los datos validados
        $validatedData['slug'] = $slug;

        // Crear y guardar la compañía
        Compania::create($validatedData);

        // Redirigir al listado con un mensaje de éxito
        return redirect()->route('companias.index')->with('success', 'Compañía creada exitosamente.');
    }
    
    

    /**
     * Mostrar los detalles de una compañía específica.
     */
    public function show(Compania $compania)
    {
        return view('companias.show', compact('compania'));
    }

    /**
     * Muestra el formulario para editar una compañía.
     */
    public function edit(Compania $compania)

{
    $clases = array_keys(config('aseguradoras.servicios', []));
    return view('companias.edit', compact('compania', 'clases'));
}

        

    /**
     * Actualiza la información de una compañía.
     */
    public function update(Request $request, Compania $compania)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'clase' => 'required|string|max:255',
        ], [
            'nombre.required' => 'El nombre de la compañía es obligatorio.',
            'clase.required' => 'Debe seleccionar una clase de servicio.',
        ]);

    
        try {
            DB::beginTransaction();
            $compania->update($validatedData);
            DB::commit();
    
            return redirect()->route('companias.index')
                ->with('success', 'Compañía actualizada exitosamente.');
        } catch (Exception $e) {
            DB::rollBack();
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
            
            // Verificar relaciones dependientes (ejemplo)
            if ($compania->seguros()->exists()) {
                throw new Exception('No se puede eliminar la compañía porque tiene seguros asociados.');
            }

            $compania->delete();
            DB::commit();

            return redirect()->route('companias.index')
                ->with('success', 'Compañía eliminada exitosamente.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['general' => 'Error al eliminar la compañía: ' . $e->getMessage()]);
        }
    }
}