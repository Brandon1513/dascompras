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
use Livewire\Component;

class Aprobar extends Component
{
    use AuthorizesRequests;

    public Requisicion $requisicion;
    public string $comentarios = '';
    public ?Aprobacion $apPendiente = null;

    public function mount(Requisicion $requisicion): void
    {
        // Política (only users allowed to approve can view this page/component)
        $this->authorize('approve', $requisicion);

        $this->requisicion = $requisicion;

        // Cargamos todo lo que mostramos
        $this->requisicion->load([
            'solicitante',
            'departamentoRef',
            'centroCostoRef',
            'items',
            'aprobaciones.nivel',
            'aprobaciones.aprobador',
        ]);

        $this->apPendiente = $this->miAprobacionPendiente();
    }

    /** Mapa de roles "lógicos" a 1+ roles reales (Spatie) */
    private function rolesQuePuedenFirmar(string $rolAprobador): array
    {
        // ejemplo: una sola etapa puede ser firmada por varios roles reales
        $map = [
            'gerencia_alta' => ['gerencia_adm', 'gerencia_finanzas'],
        ];

        return $map[$rolAprobador] ?? [$rolAprobador];
    }

    /** Primera aprobación PENDIENTE que le corresponde al usuario actual */
    private function miAprobacionPendiente(): ?Aprobacion
    {
        $user = Auth::user();

        $q = Aprobacion::query()
            ->where('requisicion_id', $this->requisicion->id)
            ->where('estado', 'pendiente')
            ->orderBy('created_at');

        // Candidatos (asignada a la persona o por rol)
        $q->where(function ($qq) use ($user) {
            $qq->where('aprobador_id', $user->id)
               ->orWhereHas('nivel', function ($qn) {
                    $qn->whereRaw('1=1'); // placeholder para mantener la OR
               });
        });

        // Validación final en PHP (por mapeo de roles)
        $ap = $q->with('nivel')->get()->first(function ($ap) use ($user) {
            if ($ap->aprobador_id === $user->id) return true;
            if (!$ap->nivel) return false;

            $roles = $this->rolesQuePuedenFirmar($ap->nivel->rol_aprobador);
            return $user->hasAnyRole($roles);
        });

        return $ap;
    }

    public function approve()
    {
        $siguiente = null;

        DB::transaction(function () use (&$siguiente) {
            $ap = $this->miAprobacionPendiente();
            abort_unless($ap, 403);

            $ap->update([
                'estado'       => 'aprobada',
                'comentarios'  => $this->comentarios,
                'firmado_en'   => now(),
                'ip'           => request()->ip(),
                'aprobador_id' => $ap->aprobador_id ?: Auth::id(),
            ]);

            // ¿Queda alguien pendiente?
            $siguiente = Aprobacion::where('requisicion_id', $this->requisicion->id)
                ->where('estado', 'pendiente')
                ->orderBy('created_at')
                ->first();

            // Si nadie más está pendiente, cerrar la requisición
            if (!$siguiente) {
                $this->requisicion->update(['estado' => 'aprobada_final']);
            }
        });

        // --- NOTIFICACIONES ---
        if ($siguiente) {
            // Notifica al siguiente aprobador (por persona o por rol)
            app(FlujoAprobacionService::class)->notificarSiguiente($this->requisicion);
        } else {
            // Notifica aprobación final al solicitante (y si quieres, a Compras)
            optional($this->requisicion->solicitante)
                ->notify(new RequisicionAprobadaFinal($this->requisicion));

            // Ejemplo: avisar a todos con rol "compras"
            User::role('compras')->get()
                ->each(fn (User $u) => $u->notify(new RequisicionAprobadaFinal($this->requisicion)));
        }

        session()->flash('status', 'Aprobada correctamente.');
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

        // Notifica al solicitante el rechazo
        optional($this->requisicion->solicitante)
            ->notify(new RequisicionRechazada($this->requisicion, $this->comentarios));

        session()->flash('status', 'Rechazada.');
        return redirect()->route('requisiciones.index');
    }

    public function render()
    {
        return view('livewire.requisiciones.aprobar', [
            'apPendiente' => $this->apPendiente,
        ]);
    }
}
