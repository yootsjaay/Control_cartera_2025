<?php
// app/Notifications/PolizaPorVencerNotification.php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class PolizaPorVencerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $poliza;
    public $diasRestantes;

    public function __construct($poliza, $diasRestantes)
    {
        $this->poliza = $poliza;
        $this->diasRestantes = $diasRestantes;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // Envía por correo y guarda en DB
    }

   // Mantén tu código actual pero mejora el toMail:
public function toMail($notifiable)
{
    $polizaPath = 'polizas_organizadas/' . 
                 $this->poliza->seguro->nombre . '/' . 
                 $this->poliza->compania->nombre . '/' . 
                 $this->poliza->ramo->nombre . '/' . 
                 "Poliza_{$this->poliza->id}.pdf";

    return (new MailMessage)
        ->subject("⚠️ Póliza por vencer: {$this->poliza->numeros_poliza->numero_poliza}")
        ->greeting("Hola {$notifiable->name}")
        ->line("La póliza del cliente {$this->poliza->nombre_cliente} está por vencer.")
        ->line("**Días restantes:** {$this->diasRestantes} días")
        ->line("**Fecha de vencimiento:** {$this->poliza->vigencia_fin->format('d/m/Y')}")
        ->line("**Compañía:** {$this->poliza->compania->nombre}")
        ->line("**Ramo:** {$this->poliza->ramo->nombre}")
        ->action('Ver detalles de la póliza', url("/polizas/{$this->poliza->id}"))
        ->attach(storage_path('app/public/' . $polizaPath))
        ->salutation('Saludos,');
}

    public function toArray($notifiable)
    {
        return [
            'poliza_id' => $this->poliza->id,
            'numero_poliza' => $this->poliza->numeros_poliza->numero_poliza,
            'cliente' => $this->poliza->nombre_cliente,
            'dias_restantes' => $this->diasRestantes,
            'fecha_vencimiento' => $this->poliza->vigencia_fin->format('d/m/Y'),
            'link' => url("/polizas/{$this->poliza->id}"),
            'tipo' => 'poliza_por_vencer'
        ];
    }
}