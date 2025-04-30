<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
{
    $polizasPorMes = DB::table('polizas')
        ->select(DB::raw('MONTH(created_at) as mes'), DB::raw('count(*) as total'))
        ->groupBy(DB::raw('MONTH(created_at)'))
        ->pluck('total', 'mes');

    $pagosPorMes = DB::table('pagos_fraccionados')
        ->select(DB::raw('MONTH(fecha_limite_pago) as mes'), DB::raw('count(*) as total'))
        ->groupBy(DB::raw('MONTH(fecha_limite_pago)'))
        ->pluck('total', 'mes');

    // Preparamos arrays con 12 posiciones (enero a diciembre)
    $polizasData = array_fill(0, 12, 0);
    $pagosData = array_fill(0, 12, 0);

    foreach ($polizasPorMes as $mes => $total) {
        $polizasData[$mes - 1] = $total;
    }

    foreach ($pagosPorMes as $mes => $total) {
        $pagosData[$mes - 1] = $total;
    }

    return view('dashboard', compact('polizasData', 'pagosData'));
}
}
