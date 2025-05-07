<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Poliza;
use App\Models\PagosFraccionado;
use App\Models\User;
use Carbon\Carbon;
use App\Notifications\PolizaPorVencerNotification;
use App\Notifications\PagoPorVencerNotification;
use Illuminate\Support\Facades\Log;

class CheckPolicyNotifications extends Command
{
    protected $signature = 'polizas:verificar-vencimientos';
    protected $description = 'Verifica pólizas y pagos próximos a vencer y envía notificaciones';

    public function handle()
    {
        $now = Carbon::now();
        
        // Notificar sobre pólizas (1 mes antes)
        $polizas = Poliza::with(['user', 'numeros_poliza', 'seguro', 'compania', 'ramo'])
            ->whereDate('vigencia_fin', '<=', $now->copy()->addMonth())
            ->whereDate('vigencia_fin', '>', $now)
            ->get();

        foreach ($polizas as $poliza) {
            $diasRestantes = $now->diffInDays($poliza->vigencia_fin);
            User::all()->each(function ($user) use ($poliza, $diasRestantes) {
                $user->notify(new PolizaPorVencerNotification($poliza, $diasRestantes));
            });
        }

        // Notificar sobre pagos (15 días antes)
        $pagos = PagosFraccionado::with(['poliza.user', 'poliza.numeros_poliza'])
            ->whereDate('fecha_limite_pago', '<=', $now->copy()->addDays(15))
            ->whereDate('fecha_limite_pago', '>', $now)
            ->get();

        foreach ($pagos as $pago) {
            $diasRestantes = $now->diffInDays($pago->fecha_limite_pago);
            User::all()->each(function ($user) use ($pago, $diasRestantes) {
                $user->notify(new PagoPorVencerNotification($pago->poliza, $pago, $diasRestantes));
            });
        }

        Log::channel('notificaciones')->info('Notificaciones procesadas', [
            'fecha' => $now->toDateTimeString(),
            'polizas' => $polizas->count(),
            'pagos' => $pagos->count()
        ]);

        $this->info("Se procesaron {$polizas->count()} pólizas y {$pagos->count()} pagos.");
    }
}