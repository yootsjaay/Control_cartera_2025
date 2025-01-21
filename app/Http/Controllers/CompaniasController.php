<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compania;
class CompaniasController extends Controller
{
    // Mostrar todas las compañias
    public function index()
    {
        $companias = Compania::all();
        return view('companias.index',compact('companias'));
    }

    // Mostrar el formulario para crear una nueva compañia
    public function create()
    {
        // Este método lo usaríamos para vistas, si tuvieras una interfaz con Blade
        return view('companias.create');
    }

    // Almacenar una nueva compañia
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:companias',
        ]);

        $compania = Compania::create([
            'nombre' => $request->nombre,
        ]);

        return response()->json($compania, 201);  // Retorna la compañia recién creada
    }

    // Mostrar una compañia específica
    public function show(Compania $compania)
    {
        return response()->json($compania);
    }

    // Mostrar el formulario para editar una compañia
    public function edit(Compania $compania)
    {
        // Este método lo usaríamos para vistas, si tuvieras una interfaz con Blade
        return view('companias.edit', compact('compania'));
    }

    // Actualizar una compañia existente
    public function update(Request $request, Compania $compania)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:companias,name,' . $compania->id,
        ]);

        $compania->update([
            'nombre' => $request->nombre,
        ]);

        return response()->json($compania);
    }

    // Eliminar una compañia
    public function destroy(Compania $compania)
    {
        $compania->delete();
        return response()->json(['message' => 'Compañía eliminada con éxito.']);
    }
}