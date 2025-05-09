<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poliza;
use App\Models\PagosFraccionado;
use Carbon\Carbon;
use DB;

class PolicyDashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $user = auth()->user();  // Obtener el usuario autenticado
        
        // Obtener los IDs de los grupos del usuario
        $groupIds = $user->groups()->pluck('groups.id'); // Obtener los IDs de los grupos

        // Pólizas próximas a vencer (1 mes antes)
        $polizasPorVencer = Poliza::with(['user:id,name', 'numeros_poliza:id,numero_poliza', 'seguro:id,nombre', 'compania:id,nombre', 'ramo:id,nombre'])
            ->whereBetween('vigencia_fin', [$now, $now->copy()->addMonth()])
            ->where(function($query) use ($user, $groupIds) {
                // Pólizas del usuario o pólizas relacionadas con los grupos del usuario
                $query->where('polizas.user_id', $user->id)
                      ->orWhereExists(function ($query) use ($groupIds) {
                          $query->select(DB::raw(1))
                                ->from('group_poliza')
                                ->whereColumn('group_poliza.poliza_id', 'polizas.id')
                                ->whereIn('group_poliza.group_id', $groupIds); // Usa el pluck de grupos aquí
                      });
            })
            ->get()
            ->map(function ($poliza) use ($now) {
                $poliza->dias_restantes = $now->diffInDays($poliza->vigencia_fin);
                return $poliza;
            });

        // Pólizas vencidas (hasta 1 día después de la fecha de vencimiento)
        $polizasVencidas = Poliza::with(['user:id,name', 'numeros_poliza:id,numero_poliza', 'seguro:id,nombre', 'compania:id,nombre', 'ramo:id,nombre'])
            ->whereBetween('vigencia_fin', [$now->copy()->subDay(), $now])
            ->where(function($query) use ($user, $groupIds) {
                // Pólizas del usuario o pólizas relacionadas con los grupos del usuario
                $query->where('polizas.user_id', $user->id)
                      ->orWhereExists(function ($query) use ($groupIds) {
                          $query->select(DB::raw(1))
                                ->from('group_poliza')
                                ->whereColumn('group_poliza.poliza_id', 'polizas.id')
                                ->whereIn('group_poliza.group_id', $groupIds);
                      });
            })
            ->get();

        // Pagos pendientes (15 días antes)
        $pagosPendientes = PagosFraccionado::with(['poliza:id,numeros_poliza_id', 'poliza.user:id,name'])
            ->whereBetween('fecha_limite_pago', [$now, $now->copy()->addDays(15)])
            ->whereHas('poliza', function($query) use ($user, $groupIds) {
                // Verificar pagos relacionados con las pólizas del usuario o de sus grupos
                $query->where('polizas.user_id', $user->id)
                      ->orWhereExists(function ($query) use ($groupIds) {
                          $query->select(DB::raw(1))
                                ->from('group_poliza')
                                ->whereColumn('group_poliza.poliza_id', 'polizas.id')
                                ->whereIn('group_poliza.group_id', $groupIds);
                      });
            })
            ->get()
            ->map(function ($pago) use ($now) {
                $pago->dias_restantes = $now->diffInDays($pago->fecha_limite_pago);
                return $pago;
            });

        return view('notificaciones.index', compact('polizasPorVencer', 'polizasVencidas', 'pagosPendientes'));
    }
}
