<?php

namespace App\Livewire\Requisiciones;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use App\Models\Requisicion;
use App\Models\Departamento;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;   

#[Layout('layouts.app')] // 👈 usa tu layout real (no 'components.layouts.app')
class Recibir extends Component
{
    use AuthorizesRequests;
    public Requisicion $requisicion;

    public string $fecha_recibido;
    public string $area_recibe = '';
    public array $departamentos = [];

     public function mount(Requisicion $requisicion): void
    {
        // Seguridad por policy (ya valida "aprobada_final" + quién puede)
        $this->authorize('receive', $requisicion);

        $this->requisicion   = $requisicion;

        // Prefills
        $this->fecha_recibido = now()->toDateString();
        $this->area_recibe    = $requisicion->departamentoRef?->nombre ?? '';

        // Para el select de áreas (si lo usas)
        $this->departamentos = \App\Models\Departamento::orderBy('nombre')
            ->get(['id','nombre'])
            ->map(fn($d) => ['id' => $d->id, 'nombre' => $d->nombre])
            ->toArray();
    }

    protected function rules(): array
    {
        return [
            'fecha_recibido' => ['required','date'],
            'area_recibe'    => ['required','string','max:255'],
        ];
    }

    public function save()
    {
        $this->validate();

        // Guardado: recibido_por_id = solicitante (según tu requerimiento)
        $this->requisicion->update([
            'recibido_por_id' => $this->requisicion->solicitante_id,
            'fecha_recibido'  => $this->fecha_recibido,
            'area_recibe'     => $this->area_recibe,
            'estado'          => 'recibida',
        ]);

        session()->flash('status', 'Requisición marcada como recibida.');
        return redirect()->route('requisiciones.index');
    }

    public function render()
    {
        return view('livewire.requisiciones.recibir');
    }
}
