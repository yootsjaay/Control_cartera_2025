<?php

namespace App\Console;

use App\Models\PagoFraccionado;
use App\Models\Poliza;
use App\Notifications\PagoPorVencerNotification;
use App\Notifications\PolizaPorVencerNotification;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $hoy = Carbon::today();

            // Notificar pagos fraccionados próximos a vencer (ejemplo: 5 días antes)
            $pagosProximos = PagoFraccionado::whereDate('fecha_limite_pago', $hoy->addDays(5))
                ->where('status', 'Pendiente') // O el estado que corresponda
                ->get();

            foreach ($pagosProximos as $pago) {
                $diasRestantes = $hoy->diffInDays($pago->fecha_limite_pago, false);
                try {
                    $pago->poliza->user->notify(new PagoPorVencerNotification($pago->poliza, $pago, $diasRestantes));

                    // Registrar la notificación enviada (opcional)
                    $pago->poliza->notificaciones()->create([
                        'tipo_notificacion' => 'pago_por_vencer',
                        'fecha_envio' => now(),
                        'mensaje' => "Recordatorio de pago fraccionado para la póliza #{$pago->poliza->numeros_poliza->numero_poliza}, vence en {$diasRestantes} días.",
                        'user_id' => auth()->id() ?? null,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al enviar notificación de pago por vencer: ' . $e->getMessage());
                }
            }

            // Notificar pólizas próximas a vencer (ejemplo: 30 días antes)
            $polizasProximas = Poliza::whereDate('vigencia_fin', $hoy->addDays(30))->get();

            foreach ($polizasProximas as $poliza) {
                $diasRestantes = $hoy->diffInDays($poliza->vigencia_fin, false);
                try {
                    $poliza->user->notify(new PolizaPorVencerNotification($poliza, $diasRestantes));

                    // Registrar la notificación enviada (opcional)
                    $poliza->notificaciones()->create([
                        'tipo_notificacion' => 'poliza_por_vencer',
                        'fecha_envio' => now(),
                        'mensaje' => "Recordatorio de póliza #{$poliza->numeros_poliza->numero_poliza} por vencer en {$diasRestantes} días.",
                        'user_id' => auth()->id() ?? null,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al enviar notificación de póliza por vencer: ' . $e->getMessage());
                }
            }

        })->dailyAt('08:00', '15:00'); // Ejecutar esta tarea diariamente a las 10:00 AM (puedes ajustar la hora)
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}