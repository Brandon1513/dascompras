<?php

namespace App\Livewire\Requisiciones;

use App\Models\Aprobacion;
use App\Models\Requisicion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Aprobar extends Component
{
    use AuthorizesRequests;

    public Requisicion $requisicion;
    public string $comentarios = '';
    public ?Aprobacion $apPendiente = null;

    public function mount(Requisicion $requisicion): void
    {
        $this->authorize('approve', $requisicion);

        $this->requisicion = $requisicion;

        // Traemos TODO lo que mostraremos
        $this->requisicion->load([
            'solicitante',
            'departamentoRef',
            'centroCostoRef',
            'items',
            'aprobaciones.nivel',
            'aprobaciones.aprobador',
        ]);

        $this->apPendiente = $this->miAprobacionPendiente(); // para banner y botones
    }

    /** Mapa de roles "lógicos" a 1+ roles reales (Spatie) */
    private function rolesQuePuedenFirmar(string $rolAprobador): array {
    $map = ['gerencia_alta' => ['gerencia_adm','gerencia_finanzas']];
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

        // Filtramos candidatos: o bien asignada a la persona,
        // o bien por rol (lo validamos en PHP por mapeo)
        $q->where(function ($qq) use ($user) {
            $qq->where('aprobador_id', $user->id)
               ->orWhereHas('nivel', function ($qn) {
                    $qn->whereRaw('1=1'); // placeholder para mantener la OR
               });
        });

        // Mapeo final por PHP
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
        DB::transaction(function () {
            $ap = $this->miAprobacionPendiente();
            abort_unless($ap, 403);

            $ap->update([
                'estado'       => 'aprobada',
                'comentarios'  => $this->comentarios,
                'firmado_en'   => now(),
                'ip'           => request()->ip(),
                'aprobador_id' => $ap->aprobador_id ?: Auth::id(),
            ]);

            $siguiente = Aprobacion::where('requisicion_id', $this->requisicion->id)
                ->where('estado', 'pendiente')
                ->orderBy('created_at')
                ->first();

            if (!$siguiente) {
                $this->requisicion->update(['estado' => 'aprobada_final']);
            }
        });

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

        session()->flash('status', 'Rechazada.');
        return redirect()->route('requisiciones.index');
    }

    public function render()
    {
        return view('livewire.requisiciones.aprobar', [
            'apPendiente' => $this->apPendiente, // úsalo en el blade
        ]);
    }
}
