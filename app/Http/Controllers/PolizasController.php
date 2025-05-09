<?php

namespace App\Http\Controllers;


use App\Models\Compania;
use App\Models\NumerosPoliza;
use App\Models\Poliza;
use App\Models\Notification;
use App\Models\Ramo;
use App\Models\Seguro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PolizasController extends Controller
{
    public function index(Request $request): View
{
    $user = auth()->user();
    $query = Poliza::with([
        'ramo:id,nombre',
        'seguro:id,nombre',
        'numeros_poliza:id,numero_poliza',
        'compania:id,nombre',
        'pagos_fraccionados:id,poliza_id,importe,fecha_limite_pago',
        'user:id,name'
    ]);

    if (!$user->hasRole('admin')) {
        // Pluck de los ids de grupos del usuario
        $groupIds = $user->groups()->pluck('groups.id');

        // Modificamos la consulta para que utilice correctamente el pluck de grupos
        $query->where(function ($q) use ($user, $groupIds) {
            $q->where('polizas.user_id', $user->id)
              ->orWhereExists(function ($query) use ($groupIds) {
                  $query->select(DB::raw(1))
                        ->from('group_poliza')
                        ->whereColumn('group_poliza.poliza_id', 'polizas.id')
                        ->whereIn('group_poliza.group_id', $groupIds); // Utiliza la variable $groupIds aquí
              });
        });
    }


        // Filtros dinámicos (fechas, compañía, etc.)
        $this->applyFilters($query, $request);

        // Paginado y orden
        $polizas = $query->latest('vigencia_inicio')->paginate(10);

        // Datos para los filtros
        $companias = Compania::orderBy('nombre')->get(['id', 'nombre']);
        $tiposPrima = Poliza::distinct()->pluck('tipo_prima');

        return view('polizas.index', [
            'polizas' => $polizas,
            'companias' => $companias,
            'tiposPrima' => $tiposPrima,
            'filters' => $request->only(['fecha_inicio', 'fecha_fin', 'companiaFilter', 'tipoFilter', 'statusFilter'])
        ]);
    }
    protected function applyFilters($query, $request)
{
    // Validar rango de fechas si ambas están presentes
    if ($request->filled(['fecha_inicio', 'fecha_fin'])) {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        
        if ($fechaInicio > $fechaFin) {
            throw new \Exception('La fecha de inicio no puede ser mayor a la fecha final');
        }
        
        $query->whereBetween('vigencia_inicio', [$fechaInicio, $fechaFin]);
    } else {
        // Filtros individuales de fecha
        if ($request->filled('fecha_inicio')) {
            $query->where('vigencia_inicio', '>=', $request->input('fecha_inicio'));
        }
        if ($request->filled('fecha_fin')) {
            $query->where('vigencia_fin', '<=', $request->input('fecha_fin'));
        }
    }

    // Filtro por compañía con validación
    if ($request->filled('companiaFilter')) {
        $companiaId = $request->input('companiaFilter');
        if (!Compania::where('id', $companiaId)->exists()) {
            throw new \Exception('La compañía seleccionada no existe');
        }
        $query->where('compania_id', $companiaId);
    }

    // Filtro por tipo de prima con validación
    if ($request->filled('tipoFilter')) {
        $tipoPrima = $request->input('tipoFilter');
        $tiposValidos = ['Anual', 'Fraccionado']; // Ajusta según tus valores reales
        
        if (!in_array($tipoPrima, $tiposValidos)) {
            throw new \Exception('Tipo de prima no válido');
        }
        $query->where('tipo_prima', $tipoPrima);
    }

    // Filtro por estado
    if ($request->filled('statusFilter')) {
        $status = $request->input('statusFilter');
        $now = now()->format('Y-m-d');
        
        if (!in_array($status, ['vigente', 'vencida'])) {
            throw new \Exception('Estado de póliza no válido');
        }
        
        if ($status === 'vigente') {
            $query->where('vigencia_fin', '>=', $now);
        } elseif ($status === 'vencida') {
            $query->where('vigencia_fin', '<', $now);
        }
    }

    return $query;
}

    public function obtenerDatosSeguro($seguroId)
    {
        $seguro = Seguro::findOrFail($seguroId);
    
        $ramos = [];
        if ($seguro->ramo) { // Asumiendo relación directa Seguro -> Ramo
            $ramos = [$seguro->ramo];
        } elseif ($seguro->ramos) { // Si es una relación muchos a muchos
            $ramos = $seguro->ramos()->get();
        }
    
        $companias = $seguro->companias()->get(); // Asumiendo relación muchos a muchos Seguro -> Compañia
    
        return response()->json([
            'ramos' => $ramos,
            'companias' => $companias,
        ]);
    }
  
    public function create(): View
    {
        $ramos = Ramo::all()->pluck('nombre', 'id');
        $seguros = Seguro::all()->pluck('nombre', 'id');
        $numerosPolizas = NumerosPoliza::all()->pluck('numero_poliza', 'id');
        $companias = Compania::all()->pluck('nombre', 'id');

        return view('polizas.create', compact('ramos', 'seguros', 'numeros_polizas', 'companias'));
    }

   
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id', // Asegúrate de que el user_id se proporcione y sea válido
            'ramo_id' => 'required|exists:ramos,id',
            'seguro_id' => 'required|exists:seguros,id',
            'numero_poliza_id' => 'required|exists:numeros_polizas,id',
            'compania_id' => 'required|exists:companias,id',
            'nombre_cliente' => 'required|string|max:255',
            'vigencia_inicio' => 'required|date',
            'vigencia_fin' => 'required|date|after:vigencia_inicio',
            'forma_pago' => 'nullable|string|max:50',
            'prima_total' => 'required|numeric|min:0',
            'primer_pago_fraccionado' => 'nullable|date|before_or_equal:vigencia_inicio',
            'tipo_prima' => 'required|string|max:50',
            'ruta_pdf' => 'nullable|string|max:255',
        ]);

        Poliza::create($request->all());

        return Redirect::route('polizas.index')->with('success', 'Póliza creada exitosamente.');
    }

    
    public function show(Poliza $poliza): View
    {
        $poliza->load(['ramo', 'seguro', 'numeros_poliza', 'compania', 'pagos_fraccionados']);
        return view('polizas.show', compact('poliza'));
    }
    public function notificar(Poliza $poliza)
    {
        
        return response()->json(['message' => 'Notificaciones enviadas correctamente']);
    }
    public function edit(Poliza $poliza): View
    {
        $poliza->load(['ramo', 'seguro', 'numeros_poliza', 'compania']);
        $ramos = Ramo::all()->pluck('nombre', 'id');
        $seguros = Seguro::all()->pluck('nombre', 'id');
        $numeros_polizas = NumerosPoliza::all();
        $companias = Compania::all()->pluck('nombre', 'id');


        return view('polizas.edit', compact('poliza', 'ramos', 'seguros', 'numeros_polizas', 'companias'));
    }


    
  public function update(Request $request, $id)
{
    $poliza = Poliza::findOrFail($id);
    
    $validatedData = $request->validate([
        'ramo_id' => 'required|exists:ramos,id',
        'seguro_id' => 'required|exists:seguros,id',
        'compania_id' => 'required|exists:companias,id',
        'nombre_cliente' => 'required|string|max:255',
        'numero_poliza' => [
            'required',
            'string',
            'max:255',
            Rule::unique('numeros_polizas', 'numero_poliza')->ignore($poliza->numero_poliza_id)
        ],
        'vigencia_inicio' => 'required|date',
        'vigencia_fin' => 'required|date|after:vigencia_inicio',
        'forma_pago' => 'nullable|string|max:255',
        'prima_total' => 'required|numeric|min:0',
        'primer_pago_fraccionado' => 'nullable|date|before_or_equal:vigencia_inicio',
        'tipo_prima' => 'required|string|max:255',
        'ruta_pdf' => 'nullable|file|mimes:pdf|max:2048'
    ]);

    DB::beginTransaction();
    try {
        // 1. Verificar si cambió ramo, seguro o compañía
        $cambioEstructura = ($poliza->ramo_id != $request->ramo_id) || 
                          ($poliza->seguro_id != $request->seguro_id) || 
                          ($poliza->compania_id != $request->compania_id);

        // 2. Eliminar PDF antiguo si cambió la estructura o se sube nuevo archivo
        if (($cambioEstructura || $request->hasFile('ruta_pdf')) && $poliza->ruta_pdf) {
            Storage::delete($poliza->ruta_pdf);
            $validatedData['ruta_pdf'] = null; // Limpiar la ruta si cambió estructura
        }

        // 3. Actualizar número de póliza
        $numeroPoliza = NumerosPoliza::updateOrCreate(
            ['id' => $poliza->numero_poliza_id],
            ['numero_poliza' => $validatedData['numero_poliza']]
        );
        $validatedData['numero_poliza_id'] = $numeroPoliza->id;
        
        // 4. Subir nuevo PDF si se proporcionó
        if ($request->hasFile('ruta_pdf')) {
            $ramo = Ramo::find($request->ramo_id)->nombre;
            $seguro = Seguro::find($request->seguro_id)->nombre;
            $compania = Compania::find($request->compania_id)->nombre;
            
            $directorio = "polizas_organizadas/{$seguro}/{$compania}/{$ramo}";
            $nombreArchivo = "Poliza_{$poliza->id}.pdf";
            $rutaArchivo = $request->file('ruta_pdf')->storeAs($directorio, $nombreArchivo, 'public');
            $validatedData['ruta_pdf'] = $rutaArchivo;
        }
        
        // 5. Actualizar la póliza
        $poliza->update($validatedData);

        DB::commit();
        return redirect()->route('polizas.index')->with([
            'success' => 'Póliza actualizada correctamente',
            'reload' => true
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al actualizar póliza: ' . $e->getMessage());
        return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
    }
}
public function destroy(Poliza $poliza): \Illuminate\Http\RedirectResponse
{
    DB::beginTransaction();
    try {
        // Eliminar PDF asociado si existe
        if ($poliza->ruta_pdf) {
            // 1. Eliminar el archivo
            Storage::delete($poliza->ruta_pdf);
            
            // 2. Obtener la ruta del directorio
            $directorio = dirname($poliza->ruta_pdf);
            
            // 3. Verificar si el directorio está vacío
            if (count(Storage::files($directorio)) == 0 && 
                count(Storage::directories($directorio)) == 0) {
                // 4. Eliminar el directorio si está vacío
                Storage::deleteDirectory($directorio);
            }
        }
        
        // Eliminar los pagos fraccionados
        $poliza->pagos_fraccionados()->delete();
        
        // Eliminar la póliza
        $poliza->delete();
        
        DB::commit();
        return redirect()->route('polizas.index')->with('success', 'Póliza eliminada exitosamente.');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al eliminar póliza: ' . $e->getMessage());
        return redirect()->route('polizas.index')->with('error', 'Ocurrió un error al eliminar la póliza: ' . $e->getMessage());
    }
}
}
