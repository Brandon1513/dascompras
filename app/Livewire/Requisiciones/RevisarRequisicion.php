<?php

namespace App\Livewire\Requisiciones;

use App\Models\Aprobacion;
use App\Models\NotificacionInterna;
use App\Models\Requisicion;
use App\Models\RequisicionActividad;
use App\Models\RequisicionItem;
use App\Models\RequisicionItemArchivo;
use App\Models\TipoImpuesto;
use App\Models\TipoRetencion;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Notifications\RequisicionRechazadaPorCompras;
use App\Services\FlujoAprobacionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class RevisarRequisicion extends Component
{
    use AuthorizesRequests, WithFileUploads;

    public Requisicion $requisicion;

    // ── Campos editables por compras ──────────────────────────────────────
    public string  $observaciones_compras = '';
    public string  $motivo_rechazo        = '';
    public ?string $metodo_pago           = null;

    // Partidas editables
    public array $items           = [];
    public array $archivos_nuevos = [];

    // Catálogos
    public array $tipos_impuesto  = [];
    public array $tipos_retencion = [];
    public array $unidades_medida = [];

    // Totales recalculados
    public float $subtotal        = 0;
    public float $total_impuestos = 0;
    public float $total           = 0;

    // Control UI
    public bool $mostrarFormRechazo = false;

    public function mount(Requisicion $requisicion): void
    {
        abort_unless(Auth::user()->hasAnyRole(['compras', 'administrador']), 403);
        abort_unless(
            in_array($requisicion->estado, ['en_revision_compras', 'aprobada_compras', 'en_aprobacion']),
            403
        );

        $requisicion->load([
            'solicitante',
            'departamentoRef',
            'centroCostoRef',
            'items.unidadMedida',
            'items.tipoImpuesto',
            'items.archivos',
            'items.retenciones',
            'aprobaciones.nivel',
            'aprobaciones.aprobador',
        ]);

        $this->requisicion           = $requisicion;
        $this->observaciones_compras = $requisicion->observaciones_compras ?? '';
        $this->metodo_pago           = $requisicion->metodo_pago;

        $this->tipos_impuesto = TipoImpuesto::activos()
            ->get(['id', 'nombre', 'clave', 'porcentaje'])
            ->map(fn($t) => [
                'id'         => $t->id,
                'nombre'     => $t->nombre,
                'clave'      => $t->clave,
                'porcentaje' => (float) $t->porcentaje,
            ])->toArray();

        $this->tipos_retencion = TipoRetencion::activos()
            ->get(['id', 'nombre', 'clave', 'porcentaje'])
            ->map(fn($r) => [
                'id'         => $r->id,
                'nombre'     => $r->nombre,
                'clave'      => $r->clave,
                'porcentaje' => (float) $r->porcentaje,
            ])->toArray();

        $this->unidades_medida = UnidadMedida::activas()
            ->get(['id', 'nombre', 'abreviatura'])
            ->map(fn($u) => [
                'id'          => $u->id,
                'nombre'      => $u->nombre,
                'abreviatura' => $u->abreviatura,
            ])->toArray();

        $this->items = $requisicion->items->map(function ($it) {
            return [
                'id'                   => $it->id,
                'descripcion'          => $it->descripcion,
                'unidad_label'         => $it->unidad_label,
                'cantidad'             => (int) $it->cantidad,
                'precio_unitario'      => (float) $it->precio_unitario,
                'subtotal'             => (float) $it->subtotal,
                'tipo_impuesto_id'     => $it->tipo_impuesto_id,
                'monto_impuesto'       => (float) $it->monto_impuesto,
                'total_item'           => (float) $it->total_item,
                'metodo_pago'          => $it->metodo_pago ?? '',
                // Retenciones — solo lectura para compras
                'retenciones_ids'      => $it->retenciones->pluck('tipo_retencion_id')->toArray(),
                'monto_retenciones'    => (float) ($it->monto_retenciones ?? 0),
                'total_neto'           => (float) ($it->total_neto ?? $it->total_item ?? 0),
                'link_compra'          => $it->link_compra,
                'proveedor_sugerido'   => $it->proveedor_sugerido,
                'archivos_existentes'  => $it->archivos->map(fn($a) => [
                    'id'              => $a->id,
                    'nombre_original' => $a->nombre_original,
                    'tipo'            => $a->tipo,
                    'url'             => Storage::disk('public')->url($a->path),
                    'icono'           => $a->icono,
                ])->toArray(),
                'ficha_tecnica_path'   => $it->ficha_tecnica_path,
                'ficha_tecnica_nombre' => $it->ficha_tecnica_nombre,
            ];
        })->toArray();

        $this->archivos_nuevos = array_fill(0, count($this->items), []);
        $this->recalcularTotales();
    }

    public function render()
    {
        return view('livewire.requisiciones.revisar-requisicion');
    }

    // ─── Recálculo ────────────────────────────────────────────────────────

    public function updatedItems(): void
    {
        $this->recalcularTotales();
    }

    private function recalcularTotales(): void
    {
        $subtotalGeneral = 0;
        $impuestoGeneral = 0;
        $impuestosMap    = collect($this->tipos_impuesto)->keyBy('id');

        foreach ($this->items as $i => $row) {
            $cant = (float) ($row['cantidad'] ?? 0);
            $pu   = (float) ($row['precio_unitario'] ?? 0);
            $sub  = round($cant * $pu, 2);

            $tipoId     = $row['tipo_impuesto_id'] ?? null;
            $porcentaje = $tipoId ? (float) ($impuestosMap[$tipoId]['porcentaje'] ?? 0) : 0;
            $montoImp   = round($sub * ($porcentaje / 100), 2);

            $this->items[$i]['subtotal']       = $sub;
            $this->items[$i]['monto_impuesto'] = $montoImp;
            $this->items[$i]['total_item']     = round($sub + $montoImp, 2);

            // Recalcular total_neto respetando retenciones existentes
            $montoRet = (float) ($row['monto_retenciones'] ?? 0);
            $this->items[$i]['total_neto'] = round($sub + $montoImp - $montoRet, 2);

            $subtotalGeneral += $sub;
            $impuestoGeneral += $montoImp;
        }

        $this->subtotal        = round($subtotalGeneral, 2);
        $this->total_impuestos = round($impuestoGeneral, 2);
        $this->total           = round($subtotalGeneral + $impuestoGeneral, 2);
    }

    // ─── Gestión de archivos ──────────────────────────────────────────────

    public function removeArchivoExistente(int $itemIndex, int $archivoId): void
    {
        $archivo = RequisicionItemArchivo::find($archivoId);
        if (!$archivo) return;

        $item = RequisicionItem::find($archivo->requisicion_item_id);
        if (!$item || $item->requisicion_id !== $this->requisicion->id) return;

        Storage::disk('public')->delete($archivo->path);
        $archivo->delete();

        $this->items[$itemIndex]['archivos_existentes'] = array_values(
            array_filter(
                $this->items[$itemIndex]['archivos_existentes'],
                fn($a) => $a['id'] !== $archivoId
            )
        );
    }

    // ─── Guardar cambios ──────────────────────────────────────────────────

    public function guardarCambios(): void
    {
        $this->validate([
            'observaciones_compras'       => ['nullable', 'string', 'max:2000'],
            'metodo_pago'                 => ['nullable', 'in:transferencia,tarjeta,efectivo'],
            'items.*.precio_unitario'     => ['required', 'numeric', 'gte:0'],
            'items.*.tipo_impuesto_id'    => ['nullable', 'exists:tipos_impuesto,id'],
            'items.*.proveedor_sugerido'  => ['nullable', 'string', 'max:255'],
            'items.*.link_compra'         => ['nullable', 'string', 'max:2000'],
            'items.*.metodo_pago'         => ['nullable', 'in:transferencia,tarjeta,efectivo'],
            'archivos_nuevos.*.*'         => ['nullable', 'file', 'max:10240',
                                              'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
        ]);

        $this->recalcularTotales();

        DB::transaction(function () {
            $this->requisicion->update([
                'observaciones_compras' => $this->observaciones_compras ?: null,
                'metodo_pago'           => $this->metodo_pago,
                'subtotal'              => $this->subtotal,
                'iva'                   => $this->total_impuestos,
                'total'                 => $this->total,
            ]);

            foreach ($this->items as $i => $row) {
                $item = RequisicionItem::find($row['id']);
                if (!$item) continue;

                $item->update([
                    'precio_unitario'    => (float) $row['precio_unitario'],
                    'tipo_impuesto_id'   => $row['tipo_impuesto_id'] ?: null,
                    'monto_impuesto'     => (float) ($row['monto_impuesto'] ?? 0),
                    'total_item'         => (float) ($row['total_item'] ?? 0),
                    'subtotal'           => (float) ($row['subtotal'] ?? 0),
                    'proveedor_sugerido' => $row['proveedor_sugerido'] ?: null,
                    'link_compra'        => $row['link_compra'] ?: null,
                    'metodo_pago'        => $row['metodo_pago'] ?: null,
                    // Recalcular total_neto con el nuevo precio pero mismas retenciones
                    'total_neto'         => (float) ($row['total_neto'] ?? $row['total_item'] ?? 0),
                ]);

                $this->guardarArchivosNuevos($item, $i, $row['archivos_existentes'] ?? []);
            }

            $this->archivos_nuevos = array_fill(0, count($this->items), []);
        });

        session()->flash('status_compras', 'Cambios guardados correctamente.');
    }

    // ─── Aprobar revisión ─────────────────────────────────────────────────

    public function aprobarRevision(): void
{
    $this->validate([
        'observaciones_compras' => ['nullable', 'string', 'max:2000'],
    ]);
    try {
        DB::transaction(function () {
            $this->recalcularTotales();
            $this->requisicion->update([
                'observaciones_compras' => $this->observaciones_compras ?: null,
                'metodo_pago'           => $this->metodo_pago,
                'subtotal'              => $this->subtotal,
                'iva'                   => $this->total_impuestos,
                'total'                 => $this->total,
                'estado'                => 'aprobada_compras',
                'revisado_por_id'       => Auth::id(),
                'revisado_en'           => now(),
            ]);
            foreach ($this->items as $row) {
                $item = RequisicionItem::find($row['id']);
                if (!$item) continue;
                $item->update([
                    'precio_unitario'    => (float) $row['precio_unitario'],
                    'tipo_impuesto_id'   => $row['tipo_impuesto_id'] ?: null,
                    'monto_impuesto'     => (float) ($row['monto_impuesto'] ?? 0),
                    'total_item'         => (float) ($row['total_item'] ?? 0),
                    'subtotal'           => (float) ($row['subtotal'] ?? 0),
                    'proveedor_sugerido' => $row['proveedor_sugerido'] ?: null,
                    'link_compra'        => $row['link_compra'] ?: null,
                    'metodo_pago'        => $row['metodo_pago'] ?: null,
                    'total_neto'         => (float) ($row['total_neto'] ?? $row['total_item'] ?? 0),
                ]);
            }
            $flujo = app(FlujoAprobacionService::class);
            $flujo->crearCadenaAprobacion($this->requisicion);
            $this->requisicion->update(['estado' => 'en_aprobacion']);
            $flujo->notificarSiguiente($this->requisicion->fresh());
 
            // ← Actividad DENTRO del transaction
            RequisicionActividad::registrar(
                $this->requisicion->id,
                'revisada',
                'Aprobada por Compras y enviada a aprobaciones.',
                null,
                'en_revision_compras',
                'en_aprobacion'
            );
            NotificacionInterna::enviar(
            $this->requisicion->solicitante_id,
            'revision',
            "Tu requi {$this->requisicion->folio} fue aprobada por Compras",
            'Pasó al flujo de aprobaciones y está en proceso.',
            route('requisiciones.show', $this->requisicion),
            $this->requisicion->id
            );

        });
 
        session()->flash('status', 'Revisión aprobada. La requisición continúa al flujo de aprobaciones.');
        $this->js("window.location.href = '" . route('requisiciones.index') . "'");
    } catch (\Exception $e) {
        \Log::error('aprobarRevision error: ' . $e->getMessage());
        $this->addError('general', 'Error al aprobar: ' . $e->getMessage());
    }
}


    // ─── Rechazar revisión ────────────────────────────────────────────────

    public function toggleFormRechazo(): void
    {
        $this->mostrarFormRechazo = !$this->mostrarFormRechazo;
        $this->motivo_rechazo     = '';
    }

    public function rechazarRevision(): void
{
    $this->validate([
        'motivo_rechazo' => ['required', 'string', 'min:10', 'max:1000'],
    ], [
        'motivo_rechazo.required' => 'Debes indicar el motivo del rechazo.',
        'motivo_rechazo.min'      => 'El motivo debe tener al menos 10 caracteres.',
    ]);
 
    DB::transaction(function () {
        $this->requisicion->update([
            'estado'                 => 'rechazada_compras',
            'motivo_rechazo_compras' => $this->motivo_rechazo,
            'revisado_por_id'        => Auth::id(),
            'revisado_en'            => now(),
        ]);
 
        // ← Actividad DENTRO del transaction
        RequisicionActividad::registrar(
            $this->requisicion->id,
            'rechazada_compras',
            "Rechazada por Compras: {$this->motivo_rechazo}",
            null,
            'en_revision_compras',
            'rechazada_compras'
        );
        NotificacionInterna::enviar(
        $this->requisicion->solicitante_id,
        'rechazada_compras',
        "Tu requi {$this->requisicion->folio} fue rechazada por Compras",
        "Motivo: {$this->motivo_rechazo}",
        route('requisiciones.edit', $this->requisicion),
        $this->requisicion->id
        );

    });
 
    optional($this->requisicion->solicitante)
        ->notify(new RequisicionRechazadaPorCompras($this->requisicion, $this->motivo_rechazo));
 
    session()->flash('status', 'Requisición rechazada. Se notificó al solicitante.');
    $this->js("window.location.href = '" . route('requisiciones.index') . "'");
}

    

    // ─── Helper archivos ──────────────────────────────────────────────────

    private function guardarArchivosNuevos(RequisicionItem $item, int $index, array $existentes): void
    {
        $uploads = $this->archivos_nuevos[$index] ?? [];
        if (empty($uploads)) return;

        $disponibles = max(0, 5 - count($existentes));

        foreach (array_slice($uploads, 0, $disponibles) as $upload) {
            if (!$upload) continue;

            $path   = $upload->store('requisiciones/archivos', 'public');
            $nombre = strtolower($upload->getClientOriginalName());
            $tipo   = str_contains($nombre, 'cotiz') ? 'cotizacion' : 'ficha_tecnica';

            RequisicionItemArchivo::create([
                'requisicion_item_id' => $item->id,
                'tipo'                => $tipo,
                'nombre_original'     => $upload->getClientOriginalName(),
                'path'                => $path,
                'mime_type'           => $upload->getMimeType(),
                'tamanio'             => $upload->getSize(),
                'subido_por_id'       => Auth::id(),
            ]);
        }
    }
}