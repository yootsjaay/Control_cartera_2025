<?php

namespace App\Http\Controllers;

use App\Models\Compania;
use App\Models\NumerosPoliza;
use App\Models\Poliza;
use App\Models\Ramo;
use App\Models\Seguro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class PolizasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $polizas = Poliza::with(['ramo', 'seguro', 'numeros_poliza', 'compania'])
            ->when($request->filled('fecha_inicio'), function ($query) use ($request) {
                $query->where('vigencia_inicio', '>=', $request->input('fecha_inicio'));
            })
            ->when($request->filled('fecha_fin'), function ($query) use ($request) {
                $query->where('vigencia_fin', '<=', $request->input('fecha_fin'));
            })
            ->when($request->filled('companiaFilter'), function ($query) use ($request) {
                $query->where('compania_id', $request->input('companiaFilter'));
            })
            ->when($request->filled('tipoFilter'), function ($query) use ($request) {
                $query->where('tipo_prima', $request->input('tipoFilter'));
            })
            ->when($request->filled('statusFilter'), function ($query) use ($request) {
                if ($request->input('statusFilter') === 'vigente') {
                    $query->where('vigencia_fin', '>', now());
                } elseif ($request->input('statusFilter') === 'vencida') {
                    $query->where('vigencia_fin', '<=', now());
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $companias = Compania::all();

        return view('polizas.index', compact('polizas', 'companias'));
    }
    public function verPdf($id)
    {
        $poliza = Poliza::with('numeros_polizas')->findOrFail($id);
    
        $ruta = $poliza->numeros_polizas->ruta_pdf;
    
        if (!file_exists($ruta)) {
            abort(404, 'Archivo no encontrado.');
        }
    
        return response()->file($ruta); // También puedes usar ->download($ruta)
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        $ramos = Ramo::all()->pluck('nombre', 'id');
        $seguros = Seguro::all()->pluck('nombre', 'id');
        $numerosPolizas = NumerosPoliza::all()->pluck('numero_poliza', 'id');
        $companias = Compania::all()->pluck('nombre', 'id');

        return view('polizas.create', compact('ramos', 'seguros', 'numeros_polizas', 'companias'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Poliza  $poliza
     * @return \Illuminate\View\View
     */
    public function show(Poliza $poliza): View
    {
        $poliza->load(['ramo', 'seguro', 'numeros_poliza', 'compania', 'pagosFraccionados']);
        return view('polizas.show', compact('poliza'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Poliza  $poliza
     * @return \Illuminate\View\View
     */
    public function edit(Poliza $poliza): View
    {
        $poliza->load(['ramo', 'seguro', 'numeros_poliza', 'compania']);
        $ramos = Ramo::all()->pluck('nombre', 'id');
        $seguros = Seguro::all()->pluck('nombre', 'id');
        $numerosPolizas = NumerosPoliza::all()->pluck('numero_poliza', 'id');
        $companias = Compania::all()->pluck('nombre', 'id');

        return view('polizas.edit', compact('poliza', 'ramos', 'seguros', 'numerosPolizas', 'companias'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Poliza  $poliza
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Poliza $poliza): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
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

        $poliza->update($request->all());

        return Redirect::route('polizas.index')->with('success', 'Póliza actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Poliza  $poliza
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Poliza $poliza): \Illuminate\Http\RedirectResponse
    {
        // Eliminar los pagos fraccionados asociados a esta póliza
        $poliza->pagos_fraccionados()->delete();

        // Luego, eliminar la póliza
        $poliza->delete();

        return Redirect::route('polizas.index')->with('success', 'Póliza eliminada exitosamente.');
    }
}
