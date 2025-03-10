<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PolizaPorVencer extends Notification
{
    use Queueable;

    protected $poliza;
   
    public function __construct($poliza)
    {
        $this->poliza = $poliza;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database']; // Notificar por correo y guardarlo en BD
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('¡Alerta de Vencimiento de Póliza!') // Correcto
                    ->greeting("Hola {$notifiable->name}") // Espacio agregado
                    ->line("Tu póliza con número {$this->poliza->numero_poliza} está por vencer el {$this->poliza->vigencia_fin}.")
                    ->action('Renovar ahora', url('/polizas/'.$this->poliza->id)) // Asegúrate de que esta ruta exista
                    ->line('Te recomendamos renovarla lo antes posible para evitar contratiempos.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'mensaje' => "Tu póliza {$this->poliza->numero_poliza} vence el {$this->poliza->vigencia_fin}.",
            'poliza_id' => $this->poliza->id
        ];
    }
}
