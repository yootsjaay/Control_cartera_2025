<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compania;

class CompaniasController extends Controller
{
    /**
     * Mostrar el listado de compañías.
     */
    public function index()
    {
        $companias = Compania::all();
    
        // Aquí defines las clases disponibles
        $clases = [
            'App\Services\QualitasSeguroService' => 'Qualitas Seguro Service',
            'App\Services\OtroSeguroService' => 'Otro Seguro Service',
        ];
    
        return view('companias.index', compact('companias', 'clases'));
    }
    

    /**
     * Muestra el formulario para crear una nueva compañía.
     */
    public function create()
    {
        return view('companias.create'); // Vista del formulario de creación
    }

    /**
     * Almacena una nueva compañía en la base de datos.
     */
    public function store(Request $request)
    {
        // Validar los datos enviados
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:companias,slug',
            'clase' => 'required|string|max:255',
        ]);

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
        return view('companias.edit', compact('compania')); // Vista del formulario de edición
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

        // Actualizar la compañía
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
