<?php

namespace App\Livewire\Requisiciones;

use App\Models\Aprobacion;
use App\Models\NotificacionInterna;
use App\Models\Requisicion;
use App\Models\RequisicionActividad;
use App\Models\User;
use App\Notifications\RequisicionAprobadaFinal;
use App\Notifications\RequisicionRechazada;
use App\Services\FlujoAprobacionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;

class Aprobar extends Component
{
    use AuthorizesRequests;

    public Requisicion $requisicion;
    public string $comentarios = '';
    public ?Aprobacion $apPendiente = null;
    public ?Aprobacion $siguientePendiente = null;
    public bool $yaFirmoEnEstaReq = false;
    public ?string $firma_base64 = null;

    public function mount(Requisicion $requisicion): void
    {
        $this->authorize('view', $requisicion);
        $this->requisicion = $requisicion;
        $this->requisicion->load([
            'solicitante', 'departamentoRef', 'centroCostoRef',
            'items.unidadMedida', 'items.tipoImpuesto', 'items.archivos',
            'aprobaciones.nivel', 'aprobaciones.aprobador',
        ]);
        $this->apPendiente = $this->miAprobacionPendiente();
        $this->yaFirmoEnEstaReq = Aprobacion::where('requisicion_id', $this->requisicion->id)
            ->where('aprobador_id', Auth::id())
            ->whereIn('estado', ['aprobada', 'rechazada'])
            ->exists();
        $this->siguientePendiente = $this->aprobacionActualPendiente();
    }

    private function rolesQuePuedenFirmar(string $rolAprobador): array
    {
        $map = ['gerencia_alta' => ['gerencia_adm', 'gerencia_finanzas']];
        return $map[$rolAprobador] ?? [$rolAprobador];
    }

    private function aprobacionActualPendiente(): ?Aprobacion
    {
        return Aprobacion::query()
            ->where('requisicion_id', $this->requisicion->id)
            ->where('estado', 'pendiente')
            ->leftJoin('niveles_aprobacion as na', 'na.id', '=', 'aprobaciones.nivel_aprobacion_id')
            ->orderBy('na.orden')
            ->orderBy('aprobaciones.id')
            ->select('aprobaciones.*')
            ->with(['nivel', 'aprobador'])
            ->first();
    }

    private function puedeFirmar(Aprobacion $ap, User $user): bool
    {
        if (!is_null($ap->aprobador_id)) {
            return (int)$ap->aprobador_id === (int)$user->id;
        }
        $rol = $ap->nivel?->rol_aprobador;
        if (!$rol) return false;
        if ($rol === 'gerente_area') {
            $gerenteId = $this->requisicion->departamentoRef()->value('gerente_id');
            if ((int)$gerenteId !== (int)$user->id) return false;
            return $user->hasRole('gerente_area');
        }
        return $user->hasAnyRole($this->rolesQuePuedenFirmar($rol));
    }

    private function miAprobacionPendiente(): ?Aprobacion
    {
        $apActual = $this->aprobacionActualPendiente();
        if (!$apActual) return null;
        return $this->puedeFirmar($apActual, Auth::user()) ? $apActual : null;
    }

    public function approve()
    {
        if (!$this->firma_base64 || !str_starts_with($this->firma_base64, 'data:image/png;base64,')) {
            $this->addError('firma_base64', 'Por favor firma antes de aprobar.');
            return;
        }

        $siguiente = null;
        $noMeToca  = false;

        DB::transaction(function () use (&$siguiente, &$noMeToca) {
            $ap = $this->miAprobacionPendiente();
            if (!$ap) { $noMeToca = true; return; }

            $png  = base64_decode(Str::after($this->firma_base64, 'data:image/png;base64,'));
            $path = "firmas/aprobaciones/req_{$this->requisicion->id}/ap_{$ap->id}.png";
            Storage::disk('public')->put($path, $png);

            $ap->update([
                'estado'       => 'aprobada',
                'comentarios'  => $this->comentarios,
                'firmado_en'   => now(),
                'ip'           => request()->ip(),
                'aprobador_id' => $ap->aprobador_id ?: Auth::id(),
                'firma_path'   => $path,
            ]);

            $siguiente = $this->aprobacionActualPendiente();

            if (!$siguiente) {
                $this->requisicion->update(['estado' => 'aprobada_final']);
            } else {
                $this->requisicion->update(['estado' => 'en_aprobacion']);
            }

            // Actividad
            RequisicionActividad::registrar(
                $this->requisicion->id,
                'aprobada',
                "Aprobada por " . Auth::user()->name . " — " . ($ap->nivel?->nombre ?? ''),
                Auth::id(),
            );
        });

        if ($noMeToca) {
            session()->flash('status', '✅ No hay aprobaciones pendientes para ti.');
            return redirect()->route('requisiciones.index');
        }

        // Notificaciones email
        if ($siguiente) {
            app(FlujoAprobacionService::class)->notificarSiguiente($this->requisicion);
        } else {
            optional($this->requisicion->solicitante)
                ->notify(new RequisicionAprobadaFinal($this->requisicion));
            User::role('compras')->get()
                ->each(fn(User $u) => $u->notify(new RequisicionAprobadaFinal($this->requisicion)));
        }

        // Notificaciones internas
        $aprobador = Auth::user();
        $nivel     = $this->apPendiente?->nivel;

        NotificacionInterna::enviar(
            $this->requisicion->solicitante_id,
            'aprobada',
            "Tu requi {$this->requisicion->folio} fue aprobada por {$aprobador->name}",
            $nivel ? "Nivel: {$nivel->nombre}" : null,
            route('requisiciones.show', $this->requisicion),
            $this->requisicion->id
        );

        if (!$siguiente) {
            NotificacionInterna::enviar(
                $this->requisicion->solicitante_id,
                'accion_requerida',
                "✅ {$this->requisicion->folio} completamente aprobada",
                'Pronto recibirás los artículos. Te avisaremos cuando estén listos.',
                route('requisiciones.show', $this->requisicion),
                $this->requisicion->id
            );
        }

        session()->flash('status', '✅ Aprobada correctamente. La requisición avanzó al siguiente nivel.');
        $this->js("window.location.href = '" . route('requisiciones.index') . "'");
    }

    public function reject()
    {
        $this->validate([
            'comentarios' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'comentarios.required' => 'El motivo del rechazo es obligatorio.',
            'comentarios.min'      => 'El motivo debe tener al menos 10 caracteres.',
        ]);

        $aprobador = Auth::user();

        DB::transaction(function () use ($aprobador) {
            $ap = $this->miAprobacionPendiente();
            abort_unless($ap, 403);

            $ap->update([
                'estado'       => 'rechazada',
                'comentarios'  => $this->comentarios,
                'firmado_en'   => now(),
                'ip'           => request()->ip(),
                'aprobador_id' => $ap->aprobador_id ?: Auth::id(),
            ]);

            $this->requisicion->update(['estado' => 'rechazada']);

            // Actividad
            RequisicionActividad::registrar(
                $this->requisicion->id,
                'rechazada',
                "Rechazada por {$aprobador->name}: {$this->comentarios}",
                Auth::id(),
                'en_aprobacion',
                'rechazada'
            );
        });

        // Notificación email
        optional($this->requisicion->solicitante)
            ->notify(new RequisicionRechazada($this->requisicion, $this->comentarios));

        // Notificación interna
        NotificacionInterna::enviar(
            $this->requisicion->solicitante_id,
            'rechazada',
            "Tu requi {$this->requisicion->folio} fue rechazada",
            "Rechazada por {$aprobador->name}: {$this->comentarios}",
            route('requisiciones.show', $this->requisicion),
            $this->requisicion->id
        );

        session()->flash('status', '⛔ Rechazada. Se notificó al solicitante con el motivo.');
        $this->js("window.location.href = '" . route('requisiciones.index') . "'");
    }

    public function render()
    {
        return view('livewire.requisiciones.aprobar', [
            'apPendiente' => $this->apPendiente,
        ]);
    }
}