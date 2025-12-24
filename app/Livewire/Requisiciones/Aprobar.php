<?php

namespace App\Livewire\Requisiciones;

use App\Models\Aprobacion;
use App\Models\Requisicion;
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
        // view para que no truene si recarga y ya no le toca
        $this->authorize('view', $requisicion);

        $this->requisicion = $requisicion;

        $this->requisicion->load([
            'solicitante',
            'departamentoRef',
            'centroCostoRef',
            'items',
            'aprobaciones.nivel',
            'aprobaciones.aprobador',
        ]);

        $this->apPendiente = $this->miAprobacionPendiente();

        $this->yaFirmoEnEstaReq = Aprobacion::where('requisicion_id', $this->requisicion->id)
            ->where('aprobador_id', Auth::id())
            ->whereIn('estado', ['aprobada', 'rechazada'])
            ->exists();

        $this->siguientePendiente = $this->aprobacionActualPendiente();
    }

    /** Mapa de roles lógicos → roles reales (Spatie) */
    private function rolesQuePuedenFirmar(string $rolAprobador): array
    {
        $map = [
            'gerencia_alta' => ['gerencia_adm', 'gerencia_finanzas'],
        ];

        return $map[$rolAprobador] ?? [$rolAprobador];
    }

    /** ✅ Obtiene SOLO la aprobación actual: primera pendiente por orden de nivel */
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

    /** ✅ Valida si el usuario puede firmar ESA aprobación */
    private function puedeFirmar(Aprobacion $ap, User $user): bool
    {
        // Si está asignada a un usuario específico, solo él
        if (!is_null($ap->aprobador_id)) {
            return (int)$ap->aprobador_id === (int)$user->id;
        }

        // Si es por rol:
        $rol = $ap->nivel?->rol_aprobador;
        if (!$rol) return false;

        // Caso especial: gerente_area por rol (por si alguna vez queda null)
        if ($rol === 'gerente_area') {
            $gerenteId = $this->requisicion->departamentoRef()->value('gerente_id');
            if ((int)$gerenteId !== (int)$user->id) return false;
            return $user->hasRole('gerente_area');
        }

        $roles = $this->rolesQuePuedenFirmar($rol);
        return $user->hasAnyRole($roles);
    }

    /** ✅ La aprobación pendiente que me corresponde (solo si soy el que debe firmar AHORA) */
    private function miAprobacionPendiente(): ?Aprobacion
    {
        $user = Auth::user();
        $apActual = $this->aprobacionActualPendiente();
        if (!$apActual) return null;

        return $this->puedeFirmar($apActual, $user) ? $apActual : null;
    }

    public function approve()
    {
        if (!$this->firma_base64 || !str_starts_with($this->firma_base64, 'data:image/png;base64,')) {
            $this->addError('firma_base64', 'Por favor firma antes de aprobar.');
            return;
        }

        $siguiente = null;
        $noMeToca = false;

        DB::transaction(function () use (&$siguiente, &$noMeToca) {
            $ap = $this->miAprobacionPendiente();

            if (!$ap) {
                $noMeToca = true;
                return;
            }

            // Guardar la firma como PNG
            $png  = base64_decode(Str::after($this->firma_base64, 'data:image/png;base64,'));
            $path = "firmas/aprobaciones/req_{$this->requisicion->id}/ap_{$ap->id}.png";
            Storage::disk('public')->put($path, $png);

            $ap->update([
                'estado'       => 'aprobada',
                'comentarios'  => $this->comentarios,
                'firmado_en'   => now(),
                'ip'           => request()->ip(),
                // si venía null (firma por rol), registra quién fue
                'aprobador_id' => $ap->aprobador_id ?: Auth::id(),
                'firma_path'   => $path,
            ]);

            // Siguiente aprobación (primera pendiente por orden)
            $siguiente = $this->aprobacionActualPendiente();

            if (!$siguiente) {
                $this->requisicion->update(['estado' => 'aprobada_final']);
            } else {
                $this->requisicion->update(['estado' => 'en_aprobacion']);
            }
        });

        if ($noMeToca) {
            session()->flash('status', '✅ No hay aprobaciones pendientes para ti (o ya avanzó el flujo).');
            return redirect()->route('requisiciones.index');
        }

        // Notificaciones
        if ($siguiente) {
            app(FlujoAprobacionService::class)->notificarSiguiente($this->requisicion);
        } else {
            optional($this->requisicion->solicitante)
                ->notify(new RequisicionAprobadaFinal($this->requisicion));

            User::role('compras')->get()
                ->each(fn (User $u) => $u->notify(new RequisicionAprobadaFinal($this->requisicion)));
        }

        session()->flash('status', '✅ Aprobada correctamente. La requisición avanzó al siguiente nivel.');
        return redirect()->route('requisiciones.index');
    }

    public function reject()
    {
        DB::transaction(function () {
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
        });

        optional($this->requisicion->solicitante)
            ->notify(new RequisicionRechazada($this->requisicion, $this->comentarios));

        session()->flash('status', '⛔ Rechazada. Se notificó al solicitante.');
        return redirect()->route('requisiciones.index');
    }

    public function render()
    {
        return view('livewire.requisiciones.aprobar', [
            'apPendiente' => $this->apPendiente,
        ]);
    }
}
