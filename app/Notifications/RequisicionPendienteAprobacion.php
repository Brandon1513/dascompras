<?php

namespace App\Notifications;

use App\Models\Requisicion;
use App\Models\Aprobacion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequisicionPendienteAprobacion extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Requisicion $requisicion,
        public Aprobacion  $aprobacion
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Requisición {$this->requisicion->folio} pendiente de tu aprobación")
            ->greeting("Hola, {$notifiable->name}")
            ->line("Tienes una requisición pendiente de aprobación.")
            ->line("Solicitante: {$this->requisicion->solicitante?->name}")
            ->line("Total: $".number_format($this->requisicion->total,2))
            ->action('Revisar / Aprobar', route('requisiciones.show', $this->requisicion))
            ->line('Gracias.');
    }

    public function toArray($notifiable): array
    {
        return [
            'requisicion_id' => $this->requisicion->id,
            'folio'          => $this->requisicion->folio,
            'etapa'          => $this->aprobacion->nivel?->nombre ?? 'Aprobación',
        ];
    }
}
