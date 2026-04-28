<?php

namespace App\Notifications;

use App\Models\Requisicion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequisicionRechazadaPorCompras extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Requisicion $requisicion,
        public string $motivo
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Requisición {$this->requisicion->folio} — Requiere correcciones")
            ->greeting("Hola, {$notifiable->name}")
            ->line("Tu requisición **{$this->requisicion->folio}** fue revisada por el área de Compras y requiere correcciones antes de continuar.")
            ->line("**Motivo:**")
            ->line($this->motivo)
            ->action('Ver y corregir requisición', route('requisiciones.show', $this->requisicion))
            ->line("Una vez realizadas las correcciones, usa el botón **\"Reenviar a compras\"** para continuar con el proceso.")
            ->line("Si tienes dudas, contacta al área de Compras.");
    }

    public function toArray($notifiable): array
    {
        return [
            'requisicion_id' => $this->requisicion->id,
            'folio'          => $this->requisicion->folio,
            'tipo'           => 'rechazada_compras',
            'motivo'         => $this->motivo,
        ];
    }
}