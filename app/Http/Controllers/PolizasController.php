<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Cliente, Compania, Seguro, Poliza, Agente, Ramo};
use Illuminate\Support\Facades\{Storage, Log};
use App\Factories\SeguroFactory;
use App\Http\Requests\StorePolizaRequest;
use Exception;

class PolizasController extends Controller
{
    /**
     * Muestra el listado de pólizas paginadas.
     */
    public function index()
    {
        // Paginamos las pólizas: 10 registros por página.
        $polizas = Poliza::paginate(10);

        return view('polizas.index', [
            'polizas'   => $polizas,
            'companias' => Compania::all(),
            'seguros'   => Seguro::all(),
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva póliza.
     */
    public function create()
    {
        $clientes  = Cliente::all();
        $companias = Compania::all();
        $seguros   = Seguro::all();
        $polizas   = Poliza::all();

        return view('polizas.create', compact('clientes', 'companias', 'seguros', 'polizas'));
    }

    /**
     * Obtiene los seguros relacionados con una compañía.
     *
     * @param int $companiaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerSeguros(int $companiaId)
    {
        try {
            $seguros = Seguro::where('compania_id', $companiaId)
                ->get(['id', 'nombre']);
            return response()->json($seguros);
        } catch (Exception $e) {
            Log::error('Error al cargar los seguros: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar los seguros.'], 500);
        }
    }

    /**
     * Obtiene los ramos relacionados con un seguro.
     *
     * @param int $seguroId
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerRamos(int $seguroId)
    {
        try {
            $ramos = Ramo::where('id_seguros', $seguroId)
                ->get(['id', 'nombre_ramo']);
            return response()->json($ramos);
        } catch (Exception $e) {
            Log::error('Error al cargar los ramos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar los ramos.'], 500);
        }
    }

    /**
     * Almacena la póliza en la base de datos.
     *
     * @param StorePolizaRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StorePolizaRequest $request)
{
    try {
        $compania = Compania::find($request->compania_id);
        $seguro = Seguro::find($request->seguro_id);
        $ramo = Ramo::find($request->ramo_id);
        

        // Crear el servicio basado en la compañía
        $seguroService = SeguroFactory::crearSeguroService($compania->slug);

        // Procesar cada archivo PDF subido
        if ($request->hasFile('pdf')) {
            foreach ($request->file('pdf') as $archivo) {
                // Pasamos los valores validados sin miedo a que sean incorrectos
                $text = $seguroService->extractToData($archivo, $seguro, $ramo);
                dd($text);
                // Aquí podrías guardar la información extraída en la BD si es necesario
            }
        }

        return redirect()->route('polizas.index')->with('success', 'Póliza cargada exitosamente.');
    } catch (Exception $e) {
        \Log::error('Error al procesar el PDF: ' . $e->getMessage());

        return redirect()->back()->withErrors([
            'general' => 'Ocurrió un error al procesar el PDF. Intenta nuevamente.'
        ]);
    }
}






        
    
        // Función para convertir la fecha
        public function convertirFecha($fecha)
        {
            try {
                $fechaObj = DateTime::createFromFormat('d/m/Y', $fecha);
                if ($fechaObj === false) {
                    // Manejo de error si el formato no es válido
                    return null;
                }
                return $fechaObj->format('Y-m-d');
            } catch (Exception $e) {
                return null;
            }
        }
        

    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
     
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $poliza = Poliza::findOrFail($id);

        // Eliminar el archivo si existe
        if ($poliza->archivo_pdf && Storage::exists('public/polizas/' . $poliza->archivo_pdf)) {
            Storage::delete('public/polizas/' . $poliza->archivo_pdf);
        }

        $poliza->delete();

        return redirect()->route('polizas.index')->with('success', 'Póliza eliminada correctamente.');
    }


  
}
