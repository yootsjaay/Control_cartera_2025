<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Cliente, Compania, Seguro, Poliza, Agente, Ramo};
use Illuminate\Support\Facades\{Storage, Log, DB};
use App\Services\PolizaService;
use App\Http\Requests\StorePolizaRequest;
use Exception;
use Carbon\Carbon;

class PolizasController extends Controller
{
    protected $polizaService;

    public function __construct(PolizaService $polizaService)
    {
        $this->polizaService = $polizaService;
    }

    public function index()
    {
        // Cargar pólizas con relaciones anidadas
        $polizas = Poliza::with([
            'cliente',
            'compania',
            'seguro.ramo' 
        ])->paginate(10);

        // Datos para filtros optimizados
        return view('polizas.index', [
            'polizas' => $polizas,
            'companias' => Compania::select('id', 'nombre')->get(),
            'seguros' => Seguro::with('ramo:id,nombre')->get(['id', 'nombre', 'ramo_id']),
            'ramos' => Ramo::select('id', 'nombre')->get()
        ]);
    }

    public function create()
    {
        return view('polizas.create', [
            'clientes' => Cliente::select('id', 'nombre_completo')->get(),
            'companias' => Compania::select('id', 'nombre')->get(),
            'seguros' => Seguro::with('ramo:id,nombre')->get(['id', 'nombre', 'ramo_id']),
            'ramos' => Ramo::select('id', 'nombre')->get()
        ]);
    }

    public function obtenerRecursos(Request $request)
{
    $validated = $request->validate([
        'modelo' => 'required|in:seguro,ramo',
        'id' => 'required|integer',
    ]);

    try {
        if ($validated['modelo'] === 'seguro') {
            // Obtener seguros por ramo_id (relación directa)
            $resultados = Seguro::where('ramo_id', $validated['id'])
                             ->get(['id', 'nombre', 'ramo_id']);
        } else {
            // Obtener ramos que tienen seguros asociados a la compañía
            $resultados = Ramo::whereHas('seguros.companias', function($q) use ($validated) {
                            $q->where('companias.id', $validated['id']);
                         })
                         ->get(['id', 'nombre']);
        }

        return response()->json($resultados);

    } catch (Exception $e) {
        Log::error("Error en obtenerRecursos: ".$e->getMessage());
        return response()->json([
            'error' => 'Error al cargar datos',
            'details' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

    public function store(StorePolizaRequest $request)
    {
        try {
            DB::beginTransaction();
            $polizasCreadas = [];

            // Verificar que se hayan subido archivos PDF
            if (!$request->hasFile('pdf')) {
                throw new Exception('No se han subido archivos PDF.');
            }

            foreach ($request->file('pdf') as $archivo) {
                $poliza = $this->polizaService->crearPoliza($request, $archivo);
                $polizasCreadas[] = $poliza;
            }

            DB::commit();

            return redirect()->route('polizas.index')
                ->with('success', 'Pólizas cargadas exitosamente. Total: ' . count($polizasCreadas));
        } catch (Exception $e) {
            DB::rollBack();
            $errorMessage = 'Ocurrió un error al procesar los PDFs: ' . $e->getMessage();
            Log::error($errorMessage);

            // Personalizar el mensaje según el tipo de error
            if (str_contains($e->getMessage(), 'SQLSTATE[22001]')) {
                $errorMessage = 'Error en la base de datos: Un valor excede la longitud permitida. Contacta al administrador.';
            }

            return redirect()->back()
                ->withErrors(['general' => $errorMessage])
                ->withInput();
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

        return redirect()->route('polizas.index')->with('delete', 'Póliza eliminada correctamente.');
    }
    public function renovaciones()
    {
        $polizas = Poliza::where('renovada', '<', now())->get(); // Ejemplo
        return view('polizas.renovaciones', compact('polizas'));
    }
    
    public function vencidas()
    {
        $polizas = Poliza::where('fecha_vencimiento', '<', now())->get(); // Ajusta según tu modelo
        return view('polizas.vencidas', compact('polizas'));
    }
    
    public function pendientes()
    {
        $polizas = Poliza::where('estado', 'pendiente')->get(); // Ajusta según tu modelo
        return view('polizas.pendientes', compact('polizas'));
    }
    
    public function archivos()
    {
        $archivos = Poliza::all(); // O una lógica específica para archivos
        return view('polizas.archivos', compact('archivos'));
    }

  
}