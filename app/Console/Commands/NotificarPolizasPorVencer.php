<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Poliza;
use App\Models\User;
use App\Notifications\PolizaPorVencerNotification;
use Carbon\Carbon;

class NotificarPolizasPorVencer extends Command
{
    protected $signature = 'notificaciones:polizas-por-vencer';
    protected $description = 'Notifica automáticamente pólizas que están por vencer';

    public function handle()
    {
        $hoy = Carbon::now();
        $enNDias = $hoy->copy()->addDays(15); // Cambia a 30, 7, etc.

        $polizas = Poliza::with(['compania', 'ramo', 'seguro', 'numeros_poliza'])
            ->whereDate('vigencia_fin', '<=', $enNDias)
            ->whereDate('vigencia_fin', '>=', $hoy)
            ->get();

        foreach ($polizas as $poliza) {
            $diasRestantes = $hoy->diffInDays(Carbon::parse($poliza->vigencia_fin));

            // Aquí defines a quién notificar. Podrías notificar al agente, o a todos los admins, etc.
            $usuarios = User::where('group_id', $poliza->group_id)->get();

            foreach ($usuarios as $usuario) {
                $usuario->notify(new PolizaPorVencerNotification($poliza, $diasRestantes));
            }
        }

        $this->info('Notificaciones enviadas correctamente.');
    }
}

