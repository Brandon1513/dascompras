<?php

namespace App\Notifications;

use App\Models\Requisicion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequisicionRecibidaParaCompras extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Requisicion $requisicion) {}

    public function via($notifiable): array
    {
        return ['mail', 'database']; // quita 'database' si no quieres guardar en BD
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Requisición {$this->requisicion->folio} marcada como RECIBIDA")
            ->greeting("Hola, {$notifiable->name}")
            ->line("La requisición {$this->requisicion->folio} fue marcada como RECIBIDA por el solicitante.")
            ->line("Total: $" . number_format($this->requisicion->total, 2))
            ->action('Ver requisición', route('requisiciones.show', $this->requisicion))
            ->line('Por favor, procede con el cierre del proceso. Gracias.');
    }

    public function toArray($notifiable): array
    {
        return [
            'requisicion_id' => $this->requisicion->id,
            'folio'          => $this->requisicion->folio,
            'estado'         => $this->requisicion->estado,
        ];
    }
}
