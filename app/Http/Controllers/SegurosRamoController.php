<?php
namespace App\Http\Controllers;

use App\Models\Seguro;
use App\Models\Compania;
use App\Models\Ramo;
use Illuminate\Http\Request;

class SegurosRamoController extends Controller
{
    // Mostrar todos los seguros junto con sus ramos
    public function index()
    {
        $seguros = Seguro::with('ramos')->get();  // Trae los seguros junto con sus ramos
        return view('seguros.index', compact('seguros'));
    }

    // Mostrar el formulario para crear un seguro y ramo
    public function create()
    {
        // Recuperamos las compañias para el formulario
        $ramos= Ramo::all();
        $companias = Compania::all();
        return view('seguros.create', compact('companias', 'ramos'));
    }

    // Almacenar un nuevo seguro y sus ramos
    public function store(Request $request)
    {
        // Validación de los datos
        $request->validate([
            'nombre' => 'required|string|max:255|unique:seguros',
            'compania_id' => 'required|exists:companias,id',
            'ramos' => 'required|array',  // Acepta un arreglo de ramos
            'ramos.*.nombre_ramo' => 'required|string|max:255',  // Validación de cada ramo
        ]);

        // Crear el seguro
        $seguro = Seguro::create([
            'nombre' => $request->nombre,
            'compania_id' => $request->compania_id,
        ]);

        // Crear los ramos relacionados con este seguro
        foreach ($request->ramos as $ramoData) {
            $seguro->ramos()->create([
                'nombre_ramo' => $ramoData['nombre_ramo'],
            ]);
        }

        return redirect()->route('seguros.index')->with('success', 'Seguro y ramos creados correctamente');
    }

    // Mostrar un seguro con sus ramos específicos
    public function show(Seguro $seguro)
    {
        // Cargamos los ramos relacionados con el seguro
        return view('seguros.show', compact('seguro'));
    }

    // Mostrar el formulario para editar un seguro y sus ramos
    public function edit(Seguro $seguro)
    {
        $companias = Compania::all();
        return view('seguros.edit', compact('seguro', 'companias'));
    }
    
    // Actualizar un seguro y sus ramos
    public function update(Request $request, Seguro $seguro)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:seguros,nombre,' . $seguro->id, // Cambiado 'name' a 'nombre'
            'compania_id' => 'required|exists:companias,id',
            'ramos' => 'required|array',
            'ramos.*.nombre_ramo' => 'required|string|max:255',
        ]);
    
        // Actualizar el seguro
        $seguro->update([
            'nombre' => $request->nombre,
            'compania_id' => $request->compania_id,
        ]);
    
        // Actualizar los ramos, eliminando los existentes y agregando los nuevos
        $seguro->ramos()->delete();  // Eliminar ramos antiguos
        foreach ($request->ramos as $ramoData) {
            $seguro->ramos()->create([
                'nombre_ramo' => $ramoData['nombre_ramo'],
            ]);
        }
    
        return redirect()->route('seguros.index')->with('success', 'Seguro y ramos actualizados correctamente');
    }
    
    // Eliminar un seguro y sus ramos
    public function destroy(Seguro $seguro)
    {
        $seguro->ramos()->delete();  // Primero eliminamos los ramos relacionados
        $seguro->delete();  // Luego eliminamos el seguro
        return redirect()->route('seguros.index')->with('success', 'Seguro y sus ramos eliminados con éxito.');
    }
}
