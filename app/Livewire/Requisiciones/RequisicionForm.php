<?php

namespace App\Livewire\Requisiciones;

use App\Models\User;
use App\Models\TipoImpuesto;
use App\Models\TipoRetencion;
use App\Models\UnidadMedida;
use App\Models\RequisicionItemArchivo;
use App\Notifications\RequisicionEnRevisionCompras;
use Livewire\Component;
use App\Models\Requisicion;
use App\Models\Departamento;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class RequisicionForm extends Component
{
    use WithFileUploads;

    public ?int   $requisicionId      = null;
    public bool   $isEditing          = false;
    public string $fecha_emision;
    public ?int   $departamento_id    = null;
    public ?int   $centro_costo_id    = null;
    public string $justificacion      = '';
    public string $solicitante_nombre = '';
    public bool   $es_pago_factura    = false;
    public bool   $afecta_produccion  = false;
    public string $estado_actual      = 'borrador';

    public mixed   $factura_nueva  = null;
    public ?string $factura_path   = null;
    public ?string $factura_nombre = null;

    public array $items           = [];
    public array $archivos_nuevos = [];
    public mixed $archivo_temp        = null;
    public ?int  $archivo_temp_index  = null;

    public float $subtotal          = 0;
    public float $total_impuestos   = 0;
    public float $total_retenciones = 0;
    public float $total             = 0;
    public float $total_neto        = 0;

    public array $departamentos   = [];
    public array $unidades_medida = [];
    public array $tipos_impuesto  = [];
    public array $tipos_retencion = [];

    public function mount(?int $requisicionId = null): void
    {
        $this->fecha_emision      = now()->toDateString();
        $this->solicitante_nombre = Auth::user()?->name ?? '';

        $this->departamentos = Departamento::orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn($d) => ['id' => $d->id, 'nombre' => $d->nombre])
            ->toArray();

        $this->unidades_medida = UnidadMedida::activas()
            ->get(['id', 'nombre', 'abreviatura'])
            ->map(fn($u) => ['id' => $u->id, 'nombre' => $u->nombre, 'abreviatura' => $u->abreviatura])
            ->toArray();

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

        if ($requisicionId) {
            $this->isEditing     = true;
            $this->requisicionId = $requisicionId;

            $req = Requisicion::with([
                'items.archivos',
                'items.unidadMedida',
                'items.tipoImpuesto',
                'items.tipoImpuesto2',
                'items.retenciones',
            ])->findOrFail($requisicionId);

            $esCompras     = Auth::user()->hasAnyRole(['compras', 'administrador']);
            $esSolicitante = $req->solicitante_id === Auth::id();

            abort_unless(
                ($esSolicitante && in_array($req->estado, ['borrador', 'rechazada_compras'])) ||
                ($esCompras && $req->puedeEditarCompras()),
                403
            );

            $this->estado_actual     = $req->estado;
            $this->fecha_emision     = $req->fecha_emision->toDateString();
            $this->departamento_id   = $req->departamento_id;
            $this->centro_costo_id   = $req->centro_costo_id;
            $this->justificacion     = $req->justificacion;
            $this->es_pago_factura   = (bool) $req->es_pago_factura;
            $this->afecta_produccion = $req->urgencia === 'urgente';
            $this->factura_path      = $req->factura_path;
            $this->factura_nombre    = $req->factura_nombre;
            $this->subtotal          = (float) $req->subtotal;
            $this->total             = (float) $req->total;

            $this->items = $req->items->map(function ($it) {
                return [
                    'id'                   => $it->id,
                    'descripcion'          => $it->descripcion,
                    'unidad'               => $it->unidad,
                    'unidad_medida_id'     => $it->unidad_medida_id,
                    'cantidad'             => (int) $it->cantidad,
                    'precio_unitario'      => (float) $it->precio_unitario,
                    'subtotal'             => (float) $it->subtotal,
                    'tipo_impuesto_id'     => $it->tipo_impuesto_id,
                    'monto_impuesto'       => (float) $it->monto_impuesto,
                    'tipo_impuesto_id_2'   => $it->tipo_impuesto_id_2,
                    'monto_impuesto_2'     => (float) ($it->monto_impuesto_2 ?? 0),
                    'total_item'           => (float) $it->total_item,
                    // Retenciones
                    'retenciones_ids'      => $it->retenciones->pluck('tipo_retencion_id')->toArray(),
                    'monto_retenciones'    => (float) ($it->monto_retenciones ?? 0),
                    'total_neto'           => (float) ($it->total_neto ?? 0),
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

        } else {
            $this->items           = [$this->itemVacio()];
            $this->archivos_nuevos = [[]];
        }

        $this->recalcularTotales();
    }

    private function itemVacio(): array
    {
        return [
            'id'                   => null,
            'descripcion'          => '',
            'unidad'               => '',
            'unidad_medida_id'     => null,
            'cantidad'             => 1,
            'precio_unitario'      => 0,
            'subtotal'             => 0,
            'tipo_impuesto_id'     => null,
            'monto_impuesto'       => 0,
            'tipo_impuesto_id_2'   => null,
            'monto_impuesto_2'     => 0,
            'total_item'           => 0,
            'retenciones_ids'      => [],
            'monto_retenciones'    => 0,
            'total_neto'           => 0,
            'link_compra'          => '',
            'proveedor_sugerido'   => '',
            'archivos_existentes'  => [],
            'ficha_tecnica_path'   => null,
            'ficha_tecnica_nombre' => null,
        ];
    }

    public function render()
    {
        return view('livewire.requisiciones.requisicion-form');
    }

    // ─── Acciones de partidas ─────────────────────────────────────────────────

    public function addItem(): void
    {
        $this->items[]           = $this->itemVacio();
        $this->archivos_nuevos[] = [];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) <= 1) return;

        unset($this->items[$index]);
        $this->items = array_values($this->items);

        unset($this->archivos_nuevos[$index]);
        $this->archivos_nuevos = array_values($this->archivos_nuevos);

        $this->recalcularTotales();
    }

    public function updatedArchivoTemp(): void
    {
        if (!$this->archivo_temp || $this->archivo_temp_index === null) return;

        $i          = $this->archivo_temp_index;
        $existentes = count($this->items[$i]['archivos_existentes'] ?? []);
        $nuevos     = count($this->archivos_nuevos[$i] ?? []);

        if (($existentes + $nuevos) >= 5) {
            $this->addError("archivos_nuevos.$i", 'Límite de 5 archivos alcanzado.');
            $this->archivo_temp       = null;
            $this->archivo_temp_index = null;
            return;
        }

        $this->archivos_nuevos[$i][] = $this->archivo_temp;
        $this->archivo_temp          = null;
        $this->archivo_temp_index    = null;
    }

    public function removeArchivoExistente(int $itemIndex, int $archivoId): void
    {
        $archivo = RequisicionItemArchivo::find($archivoId);
        if (!$archivo) return;

        $item = \App\Models\RequisicionItem::find($archivo->requisicion_item_id);
        if (!$item || $item->requisicion_id !== $this->requisicionId) return;

        Storage::disk('public')->delete($archivo->path);
        $archivo->delete();

        $this->items[$itemIndex]['archivos_existentes'] = array_values(
            array_filter(
                $this->items[$itemIndex]['archivos_existentes'],
                fn($a) => $a['id'] !== $archivoId
            )
        );
    }

    public function removeArchivoNuevo(int $itemIndex, int $archivoIndex): void
    {
        if (isset($this->archivos_nuevos[$itemIndex][$archivoIndex])) {
            unset($this->archivos_nuevos[$itemIndex][$archivoIndex]);
            $this->archivos_nuevos[$itemIndex] = array_values($this->archivos_nuevos[$itemIndex]);
        }
    }

    public function removeFactura(): void
    {
        if ($this->factura_path) {
            Storage::disk('public')->delete($this->factura_path);
        }
        $this->factura_path   = null;
        $this->factura_nombre = null;
        $this->factura_nueva  = null;

        if ($this->requisicionId) {
            Requisicion::where('id', $this->requisicionId)->update([
                'factura_path'   => null,
                'factura_nombre' => null,
            ]);
        }
    }

    // ─── Recálculo reactivo ───────────────────────────────────────────────────

    public function updatedItems(): void
    {
        $this->recalcularTotales();
    }

    private function recalcularTotales(): void
    {
        $subtotalGeneral  = 0;
        $impuestoGeneral  = 0;
        $retencionGeneral = 0;
        $impuestosMap     = collect($this->tipos_impuesto)->keyBy('id');
        $retencionesMap   = collect($this->tipos_retencion)->keyBy('id');

        foreach ($this->items as $i => $row) {
            $cant = (float) ($row['cantidad'] ?? 0);
            $pu   = (float) ($row['precio_unitario'] ?? 0);
            $sub  = round($cant * $pu, 2);

            // Impuesto 1
            $tipoId1 = $row['tipo_impuesto_id'] ?? null;
            $pct1    = $tipoId1 ? (float) ($impuestosMap[$tipoId1]['porcentaje'] ?? 0) : 0;
            $imp1    = round($sub * ($pct1 / 100), 2);

            // Impuesto 2
            $tipoId2 = $row['tipo_impuesto_id_2'] ?? null;
            $pct2    = $tipoId2 ? (float) ($impuestosMap[$tipoId2]['porcentaje'] ?? 0) : 0;
            $imp2    = round($sub * ($pct2 / 100), 2);

            $totalItem = round($sub + $imp1 + $imp2, 2);

            // Retenciones — solo si es pago de factura
            $montoRet = 0;
            if ($this->es_pago_factura) {
                foreach ($row['retenciones_ids'] ?? [] as $retId) {
                    $pctRet    = $retId ? (float) ($retencionesMap[$retId]['porcentaje'] ?? 0) : 0;
                    $montoRet += round($sub * ($pctRet / 100), 2);
                }
                $montoRet = round($montoRet, 2);
            }

            $totalNeto = round($totalItem - $montoRet, 2);

            $this->items[$i]['subtotal']          = $sub;
            $this->items[$i]['monto_impuesto']    = $imp1;
            $this->items[$i]['monto_impuesto_2']  = $imp2;
            $this->items[$i]['total_item']        = $totalItem;
            $this->items[$i]['monto_retenciones'] = $montoRet;
            $this->items[$i]['total_neto']        = $totalNeto;

            $subtotalGeneral  += $sub;
            $impuestoGeneral  += $imp1 + $imp2;
            $retencionGeneral += $montoRet;
        }

        $this->subtotal          = round($subtotalGeneral, 2);
        $this->total_impuestos   = round($impuestoGeneral, 2);
        $this->total_retenciones = round($retencionGeneral, 2);
        $this->total             = round($subtotalGeneral + $impuestoGeneral, 2);
        $this->total_neto        = round($this->total - $retencionGeneral, 2);
    }

    // ─── Validación ───────────────────────────────────────────────────────────

    private function rules(): array
    {
        $facturaRules = $this->es_pago_factura && !$this->factura_path
            ? ['required'] : ['nullable'];

        return [
            'fecha_emision'                 => ['required', 'date'],
            'departamento_id'               => ['required', 'exists:departamentos,id'],
            'centro_costo_id'               => ['required', 'exists:departamentos,id'],
            'justificacion'                 => ['required', 'string', 'min:5'],
            'es_pago_factura'               => ['boolean'],
            'afecta_produccion'             => ['boolean'],
            'factura_nueva'                 => array_merge($facturaRules,
                ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png']),

            'items'                         => ['required', 'array', 'min:1'],
            'items.*.descripcion'           => ['required', 'string', 'min:2', 'max:255'],
            'items.*.unidad_medida_id'      => ['nullable', 'exists:unidades_medida,id'],
            'items.*.cantidad'              => ['required', 'integer', 'min:1'],
            'items.*.precio_unitario'       => ['required', 'numeric', 'gte:0'],
            'items.*.tipo_impuesto_id'      => ['nullable', 'exists:tipos_impuesto,id'],
            'items.*.tipo_impuesto_id_2'    => ['nullable', 'exists:tipos_impuesto,id'],
            'items.*.retenciones_ids'       => ['array'],
            'items.*.retenciones_ids.*'     => ['exists:tipos_retencion,id'],
            'items.*.link_compra'           => ['nullable', 'string', 'max:2000'],
            'items.*.proveedor_sugerido'    => ['nullable', 'string', 'max:255'],

            'archivos_nuevos'               => ['array'],
            'archivos_nuevos.*'             => ['array', 'max:5'],
            'archivos_nuevos.*.*'           => ['nullable', 'file', 'max:10240',
                                               'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'factura_nueva.required' => 'Debes adjuntar la factura para continuar.',
            'factura_nueva.file'     => 'El archivo de factura no es válido.',
            'factura_nueva.max'      => 'La factura no debe superar 10 MB.',
            'factura_nueva.mimes'    => 'La factura debe ser PDF, JPG o PNG.',
        ];
    }

    private function generateFolio(Carbon $fecha): string
    {
        $prefix = 'REQ-' . $fecha->format('ym') . '-';

        $ultimo = Requisicion::whereYear('fecha_emision', $fecha->year)
            ->whereMonth('fecha_emision', $fecha->month)
            ->orderByDesc('id')
            ->value('folio');

        if ($ultimo) {
            $numero = (int) substr($ultimo, -4) + 1;
        } else {
            $numero = 1;
        }

        return $prefix . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
}

    // ─── Acciones ─────────────────────────────────────────────────────────────

    public function saveDraft(): void
    {
        $this->persist('borrador');
        session()->flash('status', 'Requisición guardada en borrador.');
        $this->js("window.location.href = '" . route('requisiciones.index') . "'");
    }

    public function sendToApproval(): void
    {
        try {
            $req = $this->persist('en_revision_compras');

            User::role('compras')->get()
                ->each(fn(User $u) => $u->notify(new RequisicionEnRevisionCompras($req, false)));

            session()->flash('status', 'Requisición enviada a revisión del área de Compras.');
            $this->js("window.location.href = '" . route('requisiciones.index') . "'");

        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, implode(' ', $messages));
            }
        } catch (\Exception $e) {
            \Log::error('sendToApproval: ' . $e->getMessage());
            $this->addError('general', 'Error al enviar: ' . $e->getMessage());
        }
    }

    public function reenviarACompras(): void
    {
        try {
            $req = Requisicion::findOrFail($this->requisicionId);
            abort_unless(
                $req->solicitante_id === Auth::id() && $req->estado === 'rechazada_compras',
                403
            );

            $req = $this->persist('en_revision_compras');

            User::role('compras')->get()
                ->each(fn(User $u) => $u->notify(new RequisicionEnRevisionCompras($req, true)));

            session()->flash('status', 'Requisición reenviada a Compras con las correcciones.');
            $this->js("window.location.href = '" . route('requisiciones.index') . "'");

        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, implode(' ', $messages));
            }
        } catch (\Exception $e) {
            \Log::error('reenviarACompras: ' . $e->getMessage());
            $this->addError('general', 'Error al reenviar: ' . $e->getMessage());
        }
    }

    // ─── Persistencia ─────────────────────────────────────────────────────────

    private function persist(string $estado): Requisicion
    {
        $this->validate($this->rules(), $this->validationMessages());
        $this->recalcularTotales();

        return DB::transaction(function () use ($estado) {

            $facturaPath   = $this->factura_path;
            $facturaNombre = $this->factura_nombre;

            if ($this->factura_nueva) {
                if ($facturaPath) Storage::disk('public')->delete($facturaPath);
                $facturaNombre = $this->factura_nueva->getClientOriginalName();
                $facturaPath   = $this->factura_nueva->store('requisiciones/facturas', 'public');
            }

            $camposBase = [
                'fecha_emision'   => $this->fecha_emision,
                'departamento_id' => $this->departamento_id,
                'centro_costo_id' => $this->centro_costo_id,
                'justificacion'   => $this->justificacion,
                'urgencia'        => $this->afecta_produccion ? 'urgente' : 'normal',
                'es_pago_factura' => $this->es_pago_factura,
                'factura_path'    => $this->es_pago_factura ? $facturaPath : null,
                'factura_nombre'  => $this->es_pago_factura ? $facturaNombre : null,
                'subtotal'        => $this->subtotal,
                'iva'             => $this->total_impuestos,
                'total'           => $this->total,
                'estado'          => $estado,
            ];

            if ($this->isEditing && $this->requisicionId) {
                $req       = Requisicion::with('items.archivos')->findOrFail($this->requisicionId);
                $esCompras = Auth::user()->hasAnyRole(['compras', 'administrador']);
                $esSol     = $req->solicitante_id === Auth::id();

                abort_unless(
                    ($esSol && in_array($req->estado, ['borrador', 'rechazada_compras'])) ||
                    ($esCompras && $req->puedeEditarCompras()),
                    403
                );

                if ($req->estado === 'rechazada_compras') {
                    $camposBase['motivo_rechazo_compras'] = null;
                }

                $req->update($camposBase);

                $req->items()->delete();

                foreach ($this->items as $i => $row) {
                    $item = $req->items()->create($this->mapItemData($row));
                    $this->guardarRetenciones($item, $row);
                    $this->guardarArchivosNuevos($item, $i, $row['archivos_existentes'] ?? []);
                }

                $this->factura_path   = $facturaPath;
                $this->factura_nombre = $facturaNombre;
                $this->factura_nueva  = null;

                return $req;

            } else {
                $fecha = Carbon::parse($this->fecha_emision);

                $req = Requisicion::create(array_merge($camposBase, [
                    'folio'          => $this->generateFolio($fecha),
                    'solicitante_id' => Auth::id(),
                ]));

                foreach ($this->items as $i => $row) {
                    $item = $req->items()->create($this->mapItemData($row));
                    $this->guardarRetenciones($item, $row);
                    $this->guardarArchivosNuevos($item, $i, []);
                }

                $this->requisicionId = $req->id;
                return $req;
            }
        });
    }

    private function mapItemData(array $row): array
    {
        return [
            'descripcion'          => $row['descripcion'],
            'unidad'               => $row['unidad'] ?: null,
            'unidad_medida_id'     => $row['unidad_medida_id'] ?: null,
            'cantidad'             => (int) $row['cantidad'],
            'precio_unitario'      => (float) $row['precio_unitario'],
            'subtotal'             => (float) ($row['subtotal'] ?? 0),
            'tipo_impuesto_id'     => $row['tipo_impuesto_id'] ?: null,
            'monto_impuesto'       => (float) ($row['monto_impuesto'] ?? 0),
            'tipo_impuesto_id_2'   => $row['tipo_impuesto_id_2'] ?: null,
            'monto_impuesto_2'     => (float) ($row['monto_impuesto_2'] ?? 0),
            'total_item'           => (float) ($row['total_item'] ?? 0),
            'monto_retenciones'    => (float) ($row['monto_retenciones'] ?? 0),
            'total_neto'           => (float) ($row['total_neto'] ?? 0),
            'link_compra'          => $row['link_compra'] ?: null,
            'proveedor_sugerido'   => $row['proveedor_sugerido'] ?: null,
            'ficha_tecnica_path'   => $row['ficha_tecnica_path'] ?? null,
            'ficha_tecnica_nombre' => $row['ficha_tecnica_nombre'] ?? null,
        ];
    }

    private function guardarRetenciones(\App\Models\RequisicionItem $item, array $row): void
    {
        if (!$this->es_pago_factura) return;

        $retencionesIds = $row['retenciones_ids'] ?? [];
        if (empty($retencionesIds)) return;

        $retencionesMap = collect($this->tipos_retencion)->keyBy('id');
        $sub            = (float) ($row['subtotal'] ?? 0);

        foreach ($retencionesIds as $retId) {
            if (!$retId) continue;
            $pct   = (float) ($retencionesMap[$retId]['porcentaje'] ?? 0);
            $monto = round($sub * ($pct / 100), 2);

            \App\Models\RequisicionItemRetencion::create([
                'requisicion_item_id' => $item->id,
                'tipo_retencion_id'   => $retId,
                'monto'               => $monto,
            ]);
        }
    }

    private function guardarArchivosNuevos(\App\Models\RequisicionItem $item, int $index, array $existentes): void
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