<?php

namespace App\Jobs;
use App\Models\Poliza;
use App\Models\User;
use App\Notifications\PolizaPorVencer;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotificarPolizasPorVencer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   
    public function __construct()
    {}
     /**
     * Execute the job.
     */
    public function handle()
    {
        $fechaAviso = Carbon::now()->addMonth()->toDateString(); // aviso un mes antes
        $polizas = Poliza::whereDate('vigencia_fin', $fechaAviso)->get();
    
        if ($polizas->isEmpty()) {
            return;
        }
    
        $usuarios = User::role('user')->get();
    
        foreach ($polizas as $poliza) {
            foreach ($usuarios as $usuario) {
                $usuario->notify(new PolizaPorVencer($poliza));
            }
        }
    }
    



}
