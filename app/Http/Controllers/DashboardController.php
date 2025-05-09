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
        $polizasDataAssoc = Poliza::selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes')
            ->toArray();
    
        $pagosDataAssoc = Poliza::join('pagos_fraccionados', 'polizas.id', '=', 'pagos_fraccionados.poliza_id')
            ->selectRaw('MONTH(pagos_fraccionados.created_at) as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes')
            ->toArray();
    
        // Asegurar que todos los meses estén presentes (con 0 si no hay datos)
        $meses = range(1, 12);
        $polizasDataAssoc = array_replace(array_fill_keys($meses, 0), $polizasDataAssoc);
        $pagosDataAssoc = array_replace(array_fill_keys($meses, 0), $pagosDataAssoc);
    
        // Convertir arrays asociativos a arrays indexados (manteniendo el orden de los meses)
        $polizasData = array_values($polizasDataAssoc);
        $pagosData = array_values($pagosDataAssoc);
    
        $polizasConPagos = Poliza::has('pagos_fraccionados')->count();
    
        $polizasPendientes = Poliza::doesntHave('pagos_fraccionados')->count();
    
        return view('dashboard', compact('polizasData', 'pagosData', 'polizasConPagos', 'polizasPendientes'));
    }
}
?>