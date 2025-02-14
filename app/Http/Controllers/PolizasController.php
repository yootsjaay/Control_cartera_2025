<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Cliente, Compania, Seguro, Poliza, Agente, Ramo};
use Illuminate\Support\Facades\{Storage, Log, DB};
use App\Services\PolizaService; // Nuevo servicio
use App\Http\Requests\StorePolizaRequest;
use Exception;
use Carbon\Carbon;
class PolizasController extends Controller
{

    protected $polizaService;

    public function __construct(PolizaService $polizaService) // Inyección de dependencias
    {
        $this->polizaService = $polizaService;
    }

    public function index()
    {
        $polizas = Poliza::paginate(10);
        return view('polizas.index', [
            'polizas'   => $polizas,
            'companias' => Compania::all(),
            'seguros'   => Seguro::all(),
        ]);
    }

    public function create()
    {
        $clientes  = Cliente::all();
        $companias = Compania::all();
        $seguros   = Seguro::all();
        $polizas   = Poliza::all();
        return view('polizas.create', compact('clientes', 'companias', 'seguros', 'polizas'));
    }

    
    public function obtenerRecursos(Request $request) // Función genérica para obtener seguros o ramos
    {
        $request->validate([
            'modelo' => 'required|in:seguro,ramo',
            'id' => 'required|integer',
        ]);

        $modelo = $request->input('modelo');
        $id = $request->input('id');

        try {
            $resultados = ($modelo === 'seguro')
                ? Seguro::where('compania_id', $id)->get(['id', 'nombre'])
                : Ramo::where('id_seguros', $id)->get(['id', 'nombre_ramo']);

            return response()->json($resultados);
        } catch (Exception $e) {
            Log::error("Error al cargar {$modelo}s: " . $e->getMessage());
            return response()->json(['error' => "Error al cargar {$modelo}s."], 500);
        }
    }

    public function store(StorePolizaRequest $request)
    {
        try {
            DB::beginTransaction();
            $polizasCreadas = []; // Array para almacenar las pólizas creadas

            foreach ($request->file('pdf') as $archivo) {
                $poliza = $this->polizaService->crearPoliza($request, $archivo); // Usar el servicio
                $polizasCreadas[] = $poliza; // Agregar la póliza creada al array
            }

            DB::commit();

            return redirect()->route('polizas.index')->with('success', 'Pólizas cargadas exitosamente.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error general al procesar PDFs: ' . $e->getMessage());
            return redirect()->back()->withErrors(['general' => 'Ocurrió un error general al procesar los PDFs: ' . $e->getMessage()]); // Mostrar mensaje de error más específico
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
