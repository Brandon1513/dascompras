<?php

namespace App\Livewire\Requisiciones;

use App\Models\Requisicion;
use App\Models\User;
use App\Notifications\RequisicionRecibidaParaCompras;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Recibir extends Component
{
    use AuthorizesRequests;

    public Requisicion $requisicion;

    public string $fecha_recibido;
    public string $area_recibe = '';
    public array  $departamentos = [];

    public function mount(Requisicion $requisicion): void
    {
        $this->authorize('receive', $requisicion);
        
        // Carga relaciones para mostrar resumen + partidas
        $requisicion->load([
            'solicitante',
            'departamentoRef',
            'centroCostoRef',
            'items',
        ]);

        $this->requisicion    = $requisicion;
        $this->fecha_recibido = now()->toDateString();
        $this->area_recibe    = $requisicion->departamentoRef?->nombre ?? '';

        $this->departamentos = \App\Models\Departamento::orderBy('nombre')
            ->get(['id','nombre'])
            ->map(fn($d) => ['id' => $d->id, 'nombre' => $d->nombre])
            ->toArray();
    }

    protected function rules(): array
    {
        return [
            'fecha_recibido' => ['required', 'date'],
            'area_recibe'    => ['required', 'string', 'max:255'],
        ];
    }

    public function save()
    {
        $this->validate();

        // Guardado
        $this->requisicion->update([
            'recibido_por_id' => $this->requisicion->solicitante_id, // el solicitante
            'fecha_recibido'  => $this->fecha_recibido,
            'area_recibe'     => $this->area_recibe,
            'estado'          => 'recibida',
        ]);

        // === Notificar al rol "compras" ===
        User::role('compras')->get()
            ->each(fn (User $u) => $u->notify(new RequisicionRecibidaParaCompras($this->requisicion)));

        session()->flash('status', 'Recepción registrada y notificada a Compras.');
        return redirect()->route('requisiciones.index');
    }

    public function render()
    {
        return view('livewire.requisiciones.recibir');
    }
}
