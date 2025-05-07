<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\PagosFraccionado;
use App\Models\Poliza;


class DashboardController extends Controller
{
    public function index()
    {
        $polizasData = Poliza::selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();
    
        $pagosData = Poliza::join('pagos_fraccionados', 'polizas.id', '=', 'pagos_fraccionados.poliza_id')
            ->selectRaw('MONTH(pagos_fraccionados.created_at) as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();
    
        $polizasConPagos = Poliza::has('pagos_fraccionados')->count();
    
        $polizasPendientes = Poliza::doesntHave('pagos_fraccionados')->count();
    
        return view('dashboard', compact('polizasData', 'pagosData', 'polizasConPagos', 'polizasPendientes'));
    }
}
