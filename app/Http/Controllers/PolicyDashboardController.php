<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poliza;
use App\Models\PagosFraccionado;
use Carbon\Carbon;

class PolicyDashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        // Pólizas próximas a vencer (1 mes antes)
        $polizasPorVencer = Poliza::with(['user', 'numeros_poliza', 'seguro', 'compania', 'ramo'])
            ->whereDate('vigencia_fin', '<=', $now->copy()->addMonth())
            ->whereDate('vigencia_fin', '>', $now)
            ->get()
            ->map(function ($poliza) use ($now) {
                $poliza->dias_restantes = $now->diffInDays($poliza->vigencia_fin);
                return $poliza;
            });

        // Pólizas vencidas (hasta 1 día después de la fecha de vencimiento)
        $polizasVencidas = Poliza::with(['user', 'numeros_poliza', 'seguro', 'compania', 'ramo'])
            ->whereDate('vigencia_fin', '<=', $now)
            ->whereDate('vigencia_fin', '>=', $now->copy()->subDay())
            ->get();

        // Pagos pendientes (15 días antes)
        $pagosPendientes = PagosFraccionado::with(['poliza.user', 'poliza.numeros_poliza'])
            ->whereDate('fecha_limite_pago', '<=', $now->copy()->addDays(15))
            ->whereDate('fecha_limite_pago', '>', $now)
            ->get()
            ->map(function ($pago) use ($now) {
                $pago->dias_restantes = $now->diffInDays($pago->fecha_limite_pago);
                return $pago;
            });

        return view('notificaciones.index', compact('polizasPorVencer', 'polizasVencidas', 'pagosPendientes'));
    }
}