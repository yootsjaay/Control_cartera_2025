<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compania;
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
        $companias = Compania::paginate(10); // Ejemplo con paginación
        
        return view('companias.index', [
            'companias' => $companias,
            'clases' => config('aseguradoras.servicios') // Accede a la configuración
        ]);
    }


    

    /**
     * Muestra el formulario para crear una nueva compañía.
     */
    public function create()
    {
        return view('companias.create',[
           
            'clases' => config('aseguradoras.servicios') // Accede a la configuración
        ]); // Vista del formulario de creación
    }

    /**
     * Almacena una nueva compañía en la base de datos.
     */
    public function store(Request $request)
    {
        // Validar los datos enviados
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'clase' => 'required|string|max:255',
        ]);

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
        return view('companias.show', compact('compania')); // Vista de detalles
    }

    /**
     * Muestra el formulario para editar una compañía.
     */
    public function edit(Compania $compania)
    {
        return view('companias.edit', [
            'compania' => $compania, // Pasamos la instancia de Compania
            'clases' => config('aseguradoras.servicios') // Pasamos las clases
        ]);
    }

    /**
     * Actualiza la información de una compañía.
     */
    public function update(Request $request, Compania $compania)
    {
        // Validar los datos actualizados
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:companias,slug,' . $compania->id,
            'clase' => 'required|string|max:255',
        ]);

        // Actualizar la compañía existente
        $compania->update($validatedData);

        // Redirigir al listado con un mensaje de éxito
        return redirect()->route('companias.index')->with('success', 'Compañía actualizada exitosamente.');
    }

    /**
     * Elimina una compañía de la base de datos.
     */
    public function destroy(Compania $compania)
    {
        $compania->delete();

        // Redirigir al listado con un mensaje de éxito
        return redirect()->route('companias.index')->with('success', 'Compañía eliminada exitosamente.');
    }
}
