<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PagoPorVencerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $poliza;
    protected $pago;
    protected $diasRestantes;

    public function __construct($poliza, $pago, $diasRestantes)
    {
        $this->poliza = $poliza;
        $this->pago = $pago;
        $this->diasRestantes = $diasRestantes;
    }
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("⏳ Pago fraccionado por vencer - Póliza {$this->poliza->numeros_poliza->numero_poliza}")
            ->greeting("Hola {$notifiable->name}")
            ->line("Tienes un pago fraccionado próximo a vencer:")
            ->line("**Cliente:** {$this->poliza->nombre_cliente}")
            ->line("**Número de recibo:** {$this->pago->numero_recibo}")
            ->line("**Fecha límite:** {$this->pago->fecha_limite_pago->format('d/m/Y')} ({$this->diasRestantes} días restantes)")
            ->line("**Importe:** $" . number_format($this->pago->importe, 2))
            ->line("**Póliza:** {$this->poliza->numeros_poliza->numero_poliza}")
            ->action('Ver detalles del pago', url("/polizas/{$this->poliza->id}/pagos"))
            ->salutation('Saludos,');
    }
    public function toArray($notifiable)
{
    return [
        'poliza_id' => $this->poliza->id,
        'numero_poliza' => $this->poliza->numeros_poliza->numero_poliza,
        'cliente' => $this->poliza->nombre_cliente,
        'monto' => $this->pago->importe,
        'fecha_limite' => $this->pago->fecha_limite_pago->format('d/m/Y'),
        'dias_restantes' => $this->diasRestantes,
        'tipo' => 'pago_por_vencer'
    ];
}
}