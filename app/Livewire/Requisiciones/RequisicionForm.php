<?php

namespace App\Livewire\Requisiciones;

use Livewire\Component;
use App\Models\Requisicion;
use App\Models\RequisicionItem;
use App\Models\Departamento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class RequisicionForm extends Component
{
    // --- Cabecera ---
    public ?int $requisicionId = null;   // id al editar
    public bool $isEditing = false;

    public string $fecha_emision;
    public string $urgencia = 'normal';
    public ?int $departamento_id = null;
    public ?int $centro_costo_id = null;
    public string $justificacion = '';
    public string $solicitante_nombre = ''; // solo UI

    // --- Partidas ---
    /** @var array<int, array{descripcion:string,unidad:?string,cantidad:float,precio_unitario:float,subtotal:float,link_compra:?string}> */
    public array $items = [];

    public float $subtotal = 0;
    public float $iva = 0;
    public float $total = 0;

    public const IVA_RATE = 0.16;

    // Para selects (como array simple para evitar serializar modelos en Livewire)
    public array $departamentos = [];

    public function mount(?int $requisicionId = null): void
    {
        $this->fecha_emision      = now()->toDateString();
        $this->solicitante_nombre = Auth::user()?->name ?? '';

        // Pasar a array (id, nombre) para Livewire
        $this->departamentos = Departamento::orderBy('nombre')
            ->get(['id','nombre'])
            ->map(fn ($d) => ['id' => $d->id, 'nombre' => $d->nombre])
            ->toArray();

        if ($requisicionId) {
            $this->isEditing     = true;
            $this->requisicionId = $requisicionId;

            $req = Requisicion::with('items')->findOrFail($requisicionId);

            // Seguridad: solo dueño y en borrador
            abort_unless($req->solicitante_id === Auth::id() && $req->estado === 'borrador', 403);

            // Prellenar
            $this->fecha_emision   = $req->fecha_emision->toDateString();
            $this->urgencia        = $req->urgencia;
            $this->departamento_id = $req->departamento_id;
            $this->centro_costo_id = $req->centro_costo_id;
            $this->justificacion   = $req->justificacion;
            $this->subtotal        = (float) $req->subtotal;
            $this->iva             = (float) $req->iva;
            $this->total           = (float) $req->total;

            $this->items = $req->items->map(function ($it) {
                return [
                    'descripcion'     => $it->descripcion,
                    'unidad'          => $it->unidad,
                    'cantidad'        => (float) $it->cantidad,
                    'precio_unitario' => (float) $it->precio_unitario,
                    'subtotal'        => (float) $it->subtotal,
                    'link_compra'     => $it->link_compra,
                ];
            })->toArray();
        } else {
            // Nuevo
            $this->items = [[
                'descripcion' => '',
                'unidad' => '',
                'cantidad' => 1,
                'precio_unitario' => 0,
                'subtotal' => 0,
                'link_compra' => '',
            ]];
        }

        $this->recalcularTotales();
    }

    public function render()
    {
        return view('livewire.requisiciones.requisicion-form');
    }

    // --- UI acciones ---
    public function addItem(): void
    {
        $this->items[] = [
            'descripcion' => '',
            'unidad' => '',
            'cantidad' => 1,
            'precio_unitario' => 0,
            'subtotal' => 0,
            'link_compra' => '',
        ];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
            $this->recalcularTotales();
        }
    }

    public function updatedItems(): void
    {
        $this->recalcularTotales();
    }

    private function recalcularTotales(): void
    {
        $subtotal = 0;
        foreach ($this->items as $i => $row) {
            $cant = (float) ($row['cantidad'] ?? 0);
            $pu   = (float) ($row['precio_unitario'] ?? 0);
            $sub  = round($cant * $pu, 2);
            $this->items[$i]['subtotal'] = $sub;
            $subtotal += $sub;
        }
        $this->subtotal = round($subtotal, 2);
        $this->iva      = round($this->subtotal * self::IVA_RATE, 2);
        $this->total    = round($this->subtotal + $this->iva, 2);
    }

    // --- Validación ---
    private function rules(): array
    {
        return [
            'fecha_emision'    => ['required', 'date'],
            'urgencia'         => ['required', 'in:normal,urgente'],
            'departamento_id'  => ['required', 'exists:departamentos,id'],
            'centro_costo_id'  => ['required', 'exists:departamentos,id'],
            'justificacion'    => ['required', 'string', 'min:5'],

            'items'                   => ['required', 'array', 'min:1'],
            'items.*.descripcion'     => ['required', 'string', 'min:2', 'max:255'],
            'items.*.unidad'          => ['nullable', 'string', 'max:20'],
            'items.*.cantidad'        => ['required', 'numeric', 'gt:0'],
            'items.*.precio_unitario' => ['required', 'numeric', 'gte:0'],
            // Si prefieres validar formato URL, cambia 'string' por 'url'
            'items.*.link_compra'     => ['nullable', 'string', 'max:255'],
        ];
    }

    private function generateFolio(Carbon $fecha): string
    {
        $prefix = 'REQ-' . $fecha->format('ym') . '-';
        $count  = Requisicion::whereYear('fecha_emision', $fecha->year)
            ->whereMonth('fecha_emision', $fecha->month)
            ->count() + 1;

        return $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    // --- Guardados / Redirects ---
    public function saveDraft(): RedirectResponse|Redirector
    {
        return $this->persist('borrador');
    }

    public function sendToApproval(): RedirectResponse|Redirector
    {
        return $this->persist('enviada');
    }

    private function persist(string $estado): RedirectResponse|Redirector
    {
        $this->validate($this->rules());
        $this->recalcularTotales();

        DB::transaction(function () use ($estado) {
            if ($this->isEditing) {
                $req = Requisicion::with('items')->findOrFail($this->requisicionId);

                // Seguridad
                abort_unless($req->solicitante_id === Auth::id() && $req->estado === 'borrador', 403);

                // Actualizar cabecera (folio se conserva)
                $req->update([
                    'fecha_emision'   => $this->fecha_emision,
                    'departamento_id' => $this->departamento_id,
                    'centro_costo_id' => $this->centro_costo_id,
                    'justificacion'   => $this->justificacion,
                    'subtotal'        => $this->subtotal,
                    'iva'             => $this->iva,
                    'total'           => $this->total,
                    'urgencia'        => $this->urgencia,
                    'estado'          => $estado,
                ]);

                // Reemplazar items
                $req->items()->delete();
                foreach ($this->items as $row) {
                    $req->items()->create([
                        'descripcion'     => $row['descripcion'],
                        'unidad'          => $row['unidad'] ?: null,
                        'cantidad'        => (float) $row['cantidad'],
                        'precio_unitario' => (float) $row['precio_unitario'],
                        'subtotal'        => (float) $row['subtotal'],
                        'link_compra'     => $row['link_compra'] ?: null,
                    ]);
                }
            } else {
                // Crear nueva
                $fecha = Carbon::parse($this->fecha_emision);

                $req = Requisicion::create([
                    'folio'           => $this->generateFolio($fecha),
                    'fecha_emision'   => $fecha->toDateString(),
                    'solicitante_id'  => Auth::id(),
                    'departamento_id' => $this->departamento_id,
                    'centro_costo_id' => $this->centro_costo_id,
                    'justificacion'   => $this->justificacion,
                    'subtotal'        => $this->subtotal,
                    'iva'             => $this->iva,
                    'total'           => $this->total,
                    'urgencia'        => $this->urgencia,
                    'estado'          => $estado,
                ]);

                foreach ($this->items as $row) {
                    $req->items()->create([
                        'descripcion'     => $row['descripcion'],
                        'unidad'          => $row['unidad'] ?: null,
                        'cantidad'        => (float) $row['cantidad'],
                        'precio_unitario' => (float) $row['precio_unitario'],
                        'subtotal'        => (float) $row['subtotal'],
                        'link_compra'     => $row['link_compra'] ?: null,
                    ]);
                }
            }
        });

        session()->flash('status', $estado === 'borrador'
            ? 'Requisición guardada en borrador.'
            : 'Requisición enviada. Pasará al flujo de aprobación.');

        // Importante: devolver el redirect (nada de ->send())
        return redirect()->route('requisiciones.index');
    }
}
