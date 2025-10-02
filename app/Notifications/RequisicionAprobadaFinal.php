<?php

namespace App\Notifications;

use App\Models\Requisicion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequisicionAprobadaFinal extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Requisicion $requisicion) {}

    public function via($notifiable): array { return ['mail','database']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Requisición {$this->requisicion->folio} aprobada")
            ->greeting("Hola, {$notifiable->name}")
            ->line("Tu requisición ha sido aprobada completamente.")
            ->action('Ver requisición', route('requisiciones.show', $this->requisicion))
            ->line('Gracias.');
    }

    public function toArray($n): array
    {
        return ['requisicion_id'=>$this->requisicion->id,'folio'=>$this->requisicion->folio];
    }
}
