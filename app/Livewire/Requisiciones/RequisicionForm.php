<?php

namespace App\Livewire\Requisiciones;

use App\Models\User;
use Livewire\Component;
use App\Models\Aprobacion;
use App\Models\Requisicion;
use App\Models\Departamento;
use Illuminate\Support\Carbon;
use App\Models\NivelAprobacion;
use App\Models\RequisicionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Services\FlujoAprobacionService;
use Illuminate\Validation\ValidationException;
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

    // Para selects
    public array $departamentos = [];

    public function mount(?int $requisicionId = null): void
    {
        $this->fecha_emision      = now()->toDateString();
        $this->solicitante_nombre = Auth::user()?->name ?? '';

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
            'items.*.link_compra'     => ['nullable', 'string', 'max:255'], // usa 'url' si quieres forzar formato
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
    public function saveDraft()
    {
        $this->persist('borrador');
        session()->flash('status', 'Requisición guardada en borrador.');
        return redirect()->route('requisiciones.index');
    }

    public function sendToApproval()
    {
        // guarda y pasa a 'en_aprobacion'
        $req = $this->persist('en_aprobacion');

        //Crear cadena de aprobacion
        $this->crearCadenaAprobacion($req);

        // notifica al siguiente aprobador
        app(FlujoAprobacionService::class)->notificarSiguiente($req);

        session()->flash('status', 'Requisición enviada. Pasará al flujo de aprobación.');
        return redirect()->route('requisiciones.index');
    }

    private function persist(string $estado): Requisicion
    {
        $this->validate($this->rules());
        $this->recalcularTotales();

        return DB::transaction(function () use ($estado) {

            if ($this->isEditing && $this->requisicionId) {
                $req = Requisicion::with('items')->findOrFail($this->requisicionId);

                // seguridad
                abort_unless($req->solicitante_id === Auth::id() && $req->estado === 'borrador', 403);

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

                $this->requisicionId = $req->id; // <-- asegúrate de guardar el id
                return $req;

            } else {
                // crear nueva
                $fecha = \Illuminate\Support\Carbon::parse($this->fecha_emision);

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

                $this->requisicionId = $req->id; // <-- guarda el id recién creado
                return $req;
            }
        });
    }

    // ---------- Cadena de aprobación ----------
    private function crearCadenaAprobacion(Requisicion $req): void
{
    // Limpia por si se reenvía
    $req->aprobaciones()->delete();

    // -------- 1) Jefe directo --------
    $nivelJefeId = NivelAprobacion::where('rol_aprobador', 'jefe')->value('id');
    $jefeId      = $this->resolverJefeDirecto($req->solicitante_id); // users.supervisor_id

    if ($jefeId && $nivelJefeId) {
        Aprobacion::create([
            'requisicion_id'      => $req->id,
            'nivel_aprobacion_id' => $nivelJefeId,
            'aprobador_id'        => $jefeId,
            'estado'              => 'pendiente',
        ]);
    } elseif (!$nivelJefeId) {
        // Lanza error claro si falta la config base
        throw ValidationException::withMessages([
            'aprobaciones' => "No existe el nivel 'jefe' en la tabla niveles_aprobacion.",
        ]);
    }

    // 1) Nivel por monto
    $nivel = NivelAprobacion::where('rol_aprobador','!=','jefe')
        ->where('monto_min','<=',$req->total)
        ->where(function($q) use ($req){
            $q->whereNull('monto_max')->orWhere('monto_max','>=',$req->total);
        })
        ->orderBy('orden')
        ->first();

    if ($nivel) {
        // Para niveles por rol (compras / operaciones) puedes dejar aprobador_id null
        // y aprobarán por pertenecer al rol. Para "gerencia_alta" igual.
        Aprobacion::create([
            'requisicion_id'      => $req->id,
            'nivel_aprobacion_id' => $nivel->id,
            'aprobador_id'        => null,     // firma por rol
            'estado'              => 'pendiente',
        ]);
    }
}

    private function resolverJefeDirecto(int $empleadoId): ?int
    {
        // Ajusta a tu esquema real (por ejemplo, columna supervisor_id en users)
        return User::where('id',$empleadoId)->value('supervisor_id');
    }

    private function findUserIdByRole(string $role): ?int
    {
        // Spatie
        return User::role($role)->value('id');
    }

    /** Devuelve el nivel cuyo rango contiene el monto (excluye jefe_directo) */
    private function nivelPorMonto(float $total): ?NivelAprobacion
    {
        return NivelAprobacion::where('rol_aprobador','!=','jefe')
            ->where('monto_min','<=',$total)
            ->where(fn($q)=>$q->where('monto_max','>=',$total)->orWhereNull('monto_max'))
            ->orderBy('orden')
            ->first();
    }
        /** Devuelve los roles reales admitidos por un nivel */
    private function rolesQuePuedenFirmar(string $rolAprobador): array
    {
        // Mapa de rol "lógico" → 1 o más roles reales de Spatie
        $map = [
            'gerencia_alta' => ['gerencia_adm','gerencia_finanzas'],
        ];

        return $map[$rolAprobador] ?? [$rolAprobador];
    }

}
