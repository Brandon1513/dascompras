<?php

namespace App\Livewire\Requisiciones;

use App\Models\Aprobacion;
use App\Models\Requisicion;
use App\Models\RequisicionItem;
use App\Models\RequisicionItemArchivo;
use App\Models\TipoImpuesto;
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
    public string  $observaciones_compras  = '';
    public string  $motivo_rechazo         = '';
    public ?string $metodo_pago            = null;

    // Partidas editables (precio, impuesto, proveedor)
    public array $items          = [];
    public array $archivos_nuevos = [];

    // Catálogos
    public array $tipos_impuesto  = [];
    public array $unidades_medida = [];

    // Totales recalculados
    public float $subtotal        = 0;
    public float $total_impuestos = 0;
    public float $total           = 0;

    // Control UI
    public bool $mostrarFormRechazo = false;

    public function mount(Requisicion $requisicion): void
    {
        // Solo compras y admin pueden acceder
        abort_unless(Auth::user()->hasAnyRole(['compras', 'administrador']), 403);

        // Solo se puede revisar si está en revisión o aprobada por compras (para seguir editando)
        abort_unless(
            in_array($requisicion->estado, [
                'en_revision_compras',
                'aprobada_compras',
                'en_aprobacion',  // compras puede ver aunque ya esté en flujo
            ]),
            403
        );

        $requisicion->load([
            'solicitante',
            'departamentoRef',
            'centroCostoRef',
            'items.unidadMedida',
            'items.tipoImpuesto',
            'items.archivos',
            'aprobaciones.nivel',
            'aprobaciones.aprobador',
        ]);

        $this->requisicion = $requisicion;

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

        $this->unidades_medida = UnidadMedida::activas()
            ->get(['id', 'nombre', 'abreviatura'])
            ->map(fn($u) => [
                'id'          => $u->id,
                'nombre'      => $u->nombre,
                'abreviatura' => $u->abreviatura,
            ])->toArray();

        // Cargar partidas editables
        $this->items = $requisicion->items->map(function ($it) {
            return [
                'id'                   => $it->id,
                'descripcion'          => $it->descripcion,
                'unidad_label'         => $it->unidad_label,
                'cantidad'             => (float) $it->cantidad,
                'precio_unitario'      => (float) $it->precio_unitario,
                'subtotal'             => (float) $it->subtotal,
                'tipo_impuesto_id'     => $it->tipo_impuesto_id,
                'monto_impuesto'       => (float) $it->monto_impuesto,
                'total_item'           => (float) $it->total_item,
                'link_compra'          => $it->link_compra,
                'proveedor_sugerido'   => $it->proveedor_sugerido,
                'archivos_existentes'  => $it->archivos->map(fn($a) => [
                    'id'              => $a->id,
                    'nombre_original' => $a->nombre_original,
                    'tipo'            => $a->tipo,
                    'tipo_label'      => $a->tipo_label,
                    'url'             => Storage::disk('public')->url($a->path),
                    'icono'           => $a->icono,
                    'tamanio'         => $a->tamanio_formateado,
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

    // ─── Recálculo de totales ─────────────────────────────────────────────

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

    // ─── Guardar cambios (sin cambiar estado) ─────────────────────────────

    public function guardarCambios(): void
    {
        $this->validate([
            'observaciones_compras'      => ['nullable', 'string', 'max:2000'],
            'metodo_pago'                => ['nullable', 'in:tarjeta,transferencia'],
            'items.*.precio_unitario'    => ['required', 'numeric', 'gte:0'],
            'items.*.tipo_impuesto_id'   => ['nullable', 'exists:tipos_impuesto,id'],
            'items.*.proveedor_sugerido' => ['nullable', 'string', 'max:255'],
            'items.*.link_compra'        => ['nullable', 'string', 'max:500'],
            'archivos_nuevos.*.*'        => ['nullable', 'file', 'max:10240',
                                             'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
        ]);

        $this->recalcularTotales();

        DB::transaction(function () {
            // Actualizar cabecera
            $this->requisicion->update([
                'observaciones_compras' => $this->observaciones_compras ?: null,
                'metodo_pago'           => $this->metodo_pago,
                'subtotal'              => $this->subtotal,
                'iva'                   => $this->total_impuestos,
                'total'                 => $this->total,
            ]);

            // Actualizar partidas (solo campos que compras puede tocar)
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
                ]);

                // Guardar nuevos archivos
                $this->guardarArchivosNuevos($item, $i, $row['archivos_existentes'] ?? []);
            }

            // Limpiar archivos temporales
            $this->archivos_nuevos = array_fill(0, count($this->items), []);
        });

        session()->flash('status_compras', 'Cambios guardados correctamente.');
    }

    // ─── Aprobar revisión de compras ──────────────────────────────────────

    public function aprobarRevision(): void
    {
        $this->validate([
            'observaciones_compras' => ['nullable', 'string', 'max:2000'],
            'metodo_pago'           => ['nullable', 'in:tarjeta,transferencia'],
        ]);

        try {
            DB::transaction(function () {
                $this->recalcularTotales();

                // Guardar ajustes finales de compras
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

                // Actualizar partidas
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
                    ]);
                }

                // Crear la cadena de aprobaciones por monto
                $flujo = app(FlujoAprobacionService::class);
                $flujo->crearCadenaAprobacion($this->requisicion);

                // Cambiar a en_aprobacion y notificar primer aprobador
                $this->requisicion->update(['estado' => 'en_aprobacion']);
                $flujo->notificarSiguiente($this->requisicion->fresh());
            });

            session()->flash('status', 'Revisión aprobada. La requisición continúa al flujo de aprobaciones.');
            $this->js("window.location.href = '" . route('requisiciones.index') . "'");

        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, implode(' ', $messages));
            }
        } catch (\Exception $e) {
            \Log::error('aprobarRevision error: ' . $e->getMessage());
            $this->addError('general', 'Error al aprobar: ' . $e->getMessage());
        }
    }

    // ─── Rechazar revisión de compras ─────────────────────────────────────

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
        });

        // Notificar al solicitante
        optional($this->requisicion->solicitante)
            ->notify(new RequisicionRechazadaPorCompras($this->requisicion, $this->motivo_rechazo));

        session()->flash('status', 'Requisición rechazada. Se notificó al solicitante.');
        $this->js("window.location.href = '" . route('requisiciones.index') . "'");
    }

    // ─── Helper archivos ──────────────────────────────────────────────────

    private function guardarArchivosNuevos(RequisicionItem $item, int $index, array $existentes): void
    {
        $uploads     = $this->archivos_nuevos[$index] ?? [];
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