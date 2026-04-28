<?php

namespace App\Notifications;

use App\Models\Requisicion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequisicionEnRevisionCompras extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Requisicion $requisicion,
        public bool $esReenvio = false  // true = el usuario corrigió y reenvió
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $asunto = $this->esReenvio
            ? "Requisición {$this->requisicion->folio} — Correcciones realizadas, pendiente de revisión"
            : "Nueva requisición {$this->requisicion->folio} pendiente de revisión";

        $linea = $this->esReenvio
            ? "El solicitante **{$this->requisicion->solicitante?->name}** realizó correcciones a la requisición y la reenvió para revisión."
            : "El solicitante **{$this->requisicion->solicitante?->name}** creó una nueva requisición pendiente de revisión.";

        return (new MailMessage)
            ->subject($asunto)
            ->greeting("Hola, {$notifiable->name}")
            ->line($linea)
            ->line("**Folio:** {$this->requisicion->folio}")
            ->line("**Total:** $" . number_format($this->requisicion->total, 2))
            ->line("**Departamento:** " . ($this->requisicion->departamentoRef?->nombre ?? '—'))
            ->line($this->requisicion->urgencia === 'urgente' ? "⚠️ **Esta requisición está marcada como URGENTE.**" : '')
            ->action('Revisar requisición', route('requisiciones.show', $this->requisicion))
            ->line('Por favor revisa que la información sea correcta antes de aprobarla.');
    }

    public function toArray($notifiable): array
    {
        return [
            'requisicion_id' => $this->requisicion->id,
            'folio'          => $this->requisicion->folio,
            'tipo'           => 'en_revision_compras',
            'es_reenvio'     => $this->esReenvio,
        ];
    }
}