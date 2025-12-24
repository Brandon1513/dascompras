<?php

namespace App\Livewire\Requisiciones;

use App\Models\Departamento;
use App\Models\Requisicion;
use App\Models\User;
use App\Notifications\RequisicionRecibidaParaCompras;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Recibir extends Component
{
    use AuthorizesRequests;

    public Requisicion $requisicion;

    public string $fecha_recibido;
    public string $area_recibe = '';
    public string $recibe_nombre = '';

    public ?string $firma_base64 = null;

    public array $departamentos = [];

    public function mount(Requisicion $requisicion): void
    {
        $this->authorize('receive', $requisicion);

        $requisicion->load([
            'solicitante',
            'departamentoRef',
            'centroCostoRef',
            'items',
        ]);

        $this->requisicion    = $requisicion;
        $this->fecha_recibido = now()->toDateString();
        $this->area_recibe    = $requisicion->departamentoRef?->nombre ?? '';

        // ✅ por default el nombre del usuario que recibe (editable)
        $this->recibe_nombre = Auth::user()?->name ?? '';

        $this->departamentos = Departamento::orderBy('nombre')
            ->get(['id','nombre'])
            ->map(fn($d) => ['id' => $d->id, 'nombre' => $d->nombre])
            ->toArray();
    }

    protected function rules(): array
    {
        return [
            'fecha_recibido'  => ['required', 'date'],
            'area_recibe'     => ['required', 'string', 'max:255'],
            'recibe_nombre'   => ['required', 'string', 'max:255'],
            'firma_base64'    => ['required', 'string'],
        ];
    }

    public function save()
    {
        $this->validate();

        // ✅ exigir firma (png base64)
        if (!$this->firma_base64 || !str_starts_with($this->firma_base64, 'data:image/png;base64,')) {
            $this->addError('firma_base64', 'Por favor firma para registrar la recepción.');
            return;
        }

        // Guardar firma como PNG
        $png  = base64_decode(Str::after($this->firma_base64, 'data:image/png;base64,'));

        $path = "firmas/recepciones/req_{$this->requisicion->id}/recepcion_" . now()->format('Ymd_His') . ".png";
        Storage::disk('public')->put($path, $png);

        // Guardado
        $this->requisicion->update([
            'recibido_por_id'      => Auth::id(),              // ✅ quien registra
            'fecha_recibido'       => $this->fecha_recibido,
            'area_recibe'          => $this->area_recibe,
            'recibe_nombre'        => $this->recibe_nombre,    // ✅ nuevo
            'firma_recepcion_path' => $path,                   // ✅ nuevo
            'estado'               => 'recibida',
        ]);

        // Notificar Compras
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
