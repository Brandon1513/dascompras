<?php

namespace App\Notifications;

use App\Models\Requisicion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequisicionRechazada extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Requisicion $requisicion, public ?string $motivo = null) {}

    public function via($n): array { return ['mail','database']; }

    public function toMail($n): MailMessage
    {
        return (new MailMessage)
            ->subject("Requisición {$this->requisicion->folio} rechazada")
            ->greeting("Hola, {$n->name}")
            ->line("Tu requisición fue rechazada.")
            ->line($this->motivo ? "Motivo: {$this->motivo}" : '')
            ->action('Ver requisición', route('requisiciones.show', $this->requisicion));
    }

    public function toArray($n): array
    {
        return ['requisicion_id'=>$this->requisicion->id,'folio'=>$this->requisicion->folio];
    }
}
