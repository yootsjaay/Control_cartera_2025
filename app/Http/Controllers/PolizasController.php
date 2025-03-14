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
        // Cargar las pólizas con las relaciones necesarias
        $polizas = Poliza::with(['compania', 'cliente', 'seguro.ramos'])->paginate(10);
    
        // Obtener compañías y seguros para filtros (solo id y nombre)
        $companias = Compania::all();
        $seguros =Seguro::all();
    
        // Obtener tipos únicos de seguros
        $tipos = Seguro::distinct()->pluck('nombre');
    
        return view('polizas.index', [
            'polizas'   => $polizas,
            'companias' => $companias,
            'seguros'   => $seguros,
            'tipos'     => $tipos, // Pasar solo los tipos de seguros a la vista
        ]);
    }
    public function create()
    {
        $clientes  = Cliente::all();
        $companias = Compania::all();
        $seguros   = Seguro::all();
        $polizas   = Poliza::all();
        return view('polizas.create', compact('clientes', 'companias', 'seguros', 'polizas'));
        dd("llegaste al metodo create");
    }

    public function obtenerRecursos(Request $request)
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