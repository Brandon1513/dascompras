<?php

namespace App\Livewire\Requisiciones;

use App\Models\User;
use Livewire\Component;
use App\Models\Aprobacion;
use App\Models\Requisicion;
use App\Models\Departamento;
use Illuminate\Support\Carbon;
use App\Models\NivelAprobacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\FlujoAprobacionService;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class RequisicionForm extends Component
{
    use WithFileUploads;

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
    /**
     * @var array<int, array{
     *   descripcion:string,
     *   unidad:?string,
     *   cantidad:float,
     *   precio_unitario:float,
     *   subtotal:float,
     *   link_compra:?string,
     *   proveedor_sugerido:?string,
     *   ficha_tecnica_path:?string,
     *   ficha_tecnica_nombre:?string
     * }>
     */
    public array $items = [];

    /**
     * Archivos por partida (NO dentro de items, porque Livewire maneja mejor arrays separados)
     * @var array<int, mixed> TemporaryUploadedFile|null
     */
    public array $fichas_tecnicas = [];

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

            // Prellenar cabecera
            $this->fecha_emision   = $req->fecha_emision->toDateString();
            $this->urgencia        = $req->urgencia;
            $this->departamento_id = $req->departamento_id;
            $this->centro_costo_id = $req->centro_costo_id;
            $this->justificacion   = $req->justificacion;
            $this->subtotal        = (float) $req->subtotal;
            $this->iva             = (float) $req->iva;
            $this->total           = (float) $req->total;

            // Partidas
            $this->items = $req->items->map(function ($it) {
                return [
                    'descripcion'          => $it->descripcion,
                    'unidad'               => $it->unidad,
                    'cantidad'             => (float) $it->cantidad,
                    'precio_unitario'      => (float) $it->precio_unitario,
                    'subtotal'             => (float) $it->subtotal,
                    'link_compra'          => $it->link_compra,
                    'proveedor_sugerido'   => $it->proveedor_sugerido,
                    'ficha_tecnica_path'   => $it->ficha_tecnica_path,
                    'ficha_tecnica_nombre' => $it->ficha_tecnica_nombre,
                ];
            })->toArray();

            // Archivos (vacío al inicio; solo se llena si suben uno nuevo)
            $this->fichas_tecnicas = array_fill(0, count($this->items), null);

        } else {
            // Nuevo
            $this->items = [[
                'descripcion'          => '',
                'unidad'               => '',
                'cantidad'             => 1,
                'precio_unitario'      => 0,
                'subtotal'             => 0,
                'link_compra'          => '',
                'proveedor_sugerido'   => '',
                'ficha_tecnica_path'   => null,
                'ficha_tecnica_nombre' => null,
            ]];

            $this->fichas_tecnicas = [null];
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
            'descripcion'          => '',
            'unidad'               => '',
            'cantidad'             => 1,
            'precio_unitario'      => 0,
            'subtotal'             => 0,
            'link_compra'          => '',
            'proveedor_sugerido'   => '',
            'ficha_tecnica_path'   => null,
            'ficha_tecnica_nombre' => null,
        ];

        $this->fichas_tecnicas[] = null;
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);

            unset($this->fichas_tecnicas[$index]);
            $this->fichas_tecnicas = array_values($this->fichas_tecnicas);

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

            'items'                       => ['required', 'array', 'min:1'],
            'items.*.descripcion'         => ['required', 'string', 'min:2', 'max:255'],
            'items.*.unidad'              => ['nullable', 'string', 'max:20'],
            'items.*.cantidad'            => ['required', 'numeric', 'gt:0'],
            'items.*.precio_unitario'     => ['required', 'numeric', 'gte:0'],
            'items.*.link_compra'         => ['nullable', 'string', 'max:255'],
            'items.*.proveedor_sugerido'  => ['nullable', 'string', 'max:255'],

            // Archivos por partida
            'fichas_tecnicas'     => ['array'],
            'fichas_tecnicas.*'   => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
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
        $req = $this->persist('en_aprobacion');

        $this->crearCadenaAprobacion($req);

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

                // BORRAMOS items y los recreamos, PERO conservando archivos si no suben nuevo
                $req->items()->delete();

                foreach ($this->items as $i => $row) {
                    $oldPath = $row['ficha_tecnica_path'] ?? null;
                    $oldName = $row['ficha_tecnica_nombre'] ?? null;

                    $upload = $this->fichas_tecnicas[$i] ?? null;

                    $finalPath = $oldPath;
                    $finalName = $oldName;

                    // Si subieron nuevo archivo para esta partida: borrar anterior + guardar nuevo
                    if ($upload) {
                        if ($oldPath) {
                            Storage::disk('public')->delete($oldPath);
                        }
                        $finalName = $upload->getClientOriginalName();
                        $finalPath = $upload->store('requisiciones/fichas', 'public');
                    }

                    $req->items()->create([
                        'descripcion'          => $row['descripcion'],
                        'unidad'               => $row['unidad'] ?: null,
                        'cantidad'             => (float) $row['cantidad'],
                        'precio_unitario'      => (float) $row['precio_unitario'],
                        'subtotal'             => (float) ($row['subtotal'] ?? 0),
                        'link_compra'          => $row['link_compra'] ?: null,
                        'proveedor_sugerido'   => $row['proveedor_sugerido'] ?: null,
                        'ficha_tecnica_path'   => $finalPath,
                        'ficha_tecnica_nombre' => $finalName,
                    ]);
                }

                $this->requisicionId = $req->id;
                return $req;

            } else {
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

                foreach ($this->items as $i => $row) {
                    $upload = $this->fichas_tecnicas[$i] ?? null;

                    $finalPath = null;
                    $finalName = null;

                    if ($upload) {
                        $finalName = $upload->getClientOriginalName();
                        $finalPath = $upload->store('requisiciones/fichas', 'public');
                    }

                    $req->items()->create([
                        'descripcion'          => $row['descripcion'],
                        'unidad'               => $row['unidad'] ?: null,
                        'cantidad'             => (float) $row['cantidad'],
                        'precio_unitario'      => (float) $row['precio_unitario'],
                        'subtotal'             => (float) ($row['subtotal'] ?? 0),
                        'link_compra'          => $row['link_compra'] ?: null,
                        'proveedor_sugerido'   => $row['proveedor_sugerido'] ?: null,
                        'ficha_tecnica_path'   => $finalPath,
                        'ficha_tecnica_nombre' => $finalName,
                    ]);
                }

                $this->requisicionId = $req->id;
                return $req;
            }
        });
    }

    // ---------- Cadena de aprobación ----------
    private function crearCadenaAprobacion(Requisicion $req): void
    {
        $req->aprobaciones()->delete();

        $nivelJefe = NivelAprobacion::query()
            ->where('rol_aprobador', 'jefe')
            ->where('activo', true)
            ->first();

        $nivelArea = NivelAprobacion::query()
            ->where('rol_aprobador', 'gerente_area')
            ->where('activo', true)
            ->first();

        if (!$nivelJefe || !$nivelArea) {
            throw ValidationException::withMessages([
                'aprobaciones' => "Faltan niveles activos base (jefe / gerente_area) en niveles_aprobacion.",
            ]);
        }

        $jefeId = $this->resolverJefeDirecto($req->solicitante_id);

        if (!$jefeId) {
            throw ValidationException::withMessages([
                'aprobaciones' => 'El solicitante no tiene jefe directo asignado (users.supervisor_id).',
            ]);
        }

        Aprobacion::create([
            'requisicion_id'      => $req->id,
            'nivel_aprobacion_id' => $nivelJefe->id,
            'aprobador_id'        => $jefeId,
            'estado'              => 'pendiente',
        ]);

        $req->loadMissing('departamentoRef');

        $gerenteAreaId = $req->departamentoRef?->gerente_id;

        if (!$gerenteAreaId) {
            throw ValidationException::withMessages([
                'aprobaciones' => 'El departamento no tiene gerente asignado (departamentos.gerente_id).',
            ]);
        }

        Aprobacion::create([
            'requisicion_id'      => $req->id,
            'nivel_aprobacion_id' => $nivelArea->id,
            'aprobador_id'        => $gerenteAreaId,
            'estado'              => 'pendiente',
        ]);

        $totalCentavos  = (int) round(((float) $req->total) * 100);
        $limiteCentavos = 500000; // 5000.00 * 100

        if ($totalCentavos > $limiteCentavos) {
            $nivelAdm = NivelAprobacion::query()
                ->where('rol_aprobador', 'gerencia_adm')
                ->where('activo', true)
                ->first();

            if (!$nivelAdm) {
                throw ValidationException::withMessages([
                    'aprobaciones' => "No existe un nivel activo para 'gerencia_adm' en niveles_aprobacion.",
                ]);
            }

            Aprobacion::create([
                'requisicion_id'      => $req->id,
                'nivel_aprobacion_id' => $nivelAdm->id,
                'aprobador_id'        => null,
                'estado'              => 'pendiente',
            ]);
        }
    }

    private function resolverJefeDirecto(int $empleadoId): ?int
    {
        return User::where('id', $empleadoId)->value('supervisor_id');
    }

    private function findUserIdByRole(string $role): ?int
    {
        return User::role($role)->value('id');
    }

    private function nivelPorMonto(float $total): ?NivelAprobacion
    {
        return NivelAprobacion::where('rol_aprobador', '!=', 'jefe')
            ->where('monto_min', '<=', $total)
            ->where(fn ($q) => $q->where('monto_max', '>=', $total)->orWhereNull('monto_max'))
            ->orderBy('orden')
            ->first();
    }

    private function rolesQuePuedenFirmar(string $rolAprobador): array
    {
        $map = [
            'gerencia_alta' => ['gerencia_adm', 'gerencia_finanzas'],
        ];

        return $map[$rolAprobador] ?? [$rolAprobador];
    }
}
