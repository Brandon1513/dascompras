<?php

namespace App\Http\Controllers;

use App\Models\Requisicion;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RequisicionController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $estado       = $request->query('estado');
        $solicitante  = $request->query('solicitante');
        $metodo_pago  = $request->query('metodo_pago');
        $pago_factura = $request->query('pago_factura');
        $user         = auth()->user();
 
        $requisiciones = Requisicion::with([
                'solicitante',
                'departamentoRef',
                'centroCostoRef',
                'items',
                'aprobaciones.nivel',
                'aprobaciones.aprobador',
            ])
            ->visibleTo($user)
            ->when($estado,      fn($q) => $q->where('estado', $estado))
            ->when($solicitante, fn($q) => $q->where('solicitante_id', $solicitante))
            ->when($metodo_pago, fn($q) => $q->where('metodo_pago', $metodo_pago))
            ->when($pago_factura !== null && $pago_factura !== '',
                fn($q) => $q->where('es_pago_factura', (bool) $pago_factura)
            )
            ->latest('id')
            ->paginate(15)
            ->appends($request->query());
 
        $solicitantes = \App\Models\User::orderBy('name')->get(['id', 'name']);
 
        return view('requisiciones.index', compact(
            'requisiciones', 'solicitantes',
            'estado', 'solicitante', 'metodo_pago', 'pago_factura',
            'user',
        ));
    }

    public function create()
    {
        return view('requisiciones.create');
    }

        public function edit(Requisicion $requisicion)
    {
        $this->authorize('update', $requisicion);
 
        // Cargar relación revisadoPor para mostrar en el banner de rechazo
        $requisicion->load(['revisadoPor']);
 
        return view('requisiciones.edit', compact('requisicion'));
    }
 


    public function show(Requisicion $requisicion)
    {
        $this->authorize('view', $requisicion);
 
        $requisicion->load([
            'solicitante',
            'departamentoRef',
            'centroCostoRef',
            'revisadoPor',              // nuevo
            'items.unidadMedida',
            'items.tipoImpuesto',
            'items.archivos',
            'items.retenciones.tipoRetencion', 
            'aprobaciones.nivel',
            'aprobaciones.aprobador',
        ]);
 
        $puedeFirmar = auth()->user()->can('approve', $requisicion);
 
        return view('requisiciones.show', compact('requisicion', 'puedeFirmar'));
    }
 


    public function recibir(Requisicion $requisicion)
    {
        $this->authorize('receive', $requisicion);

        // para tu vista recibir (resumen + tabla)
        $requisicion->load(['solicitante','departamentoRef','centroCostoRef','items','items.retenciones.tipoRetencion']);

        return view('requisiciones.recibir', compact('requisicion'));
    }

    /**
     * ✅ Guardar recepción (si decides hacerlo por POST normal)
     * Requiere columnas:
     * - fecha_recibido (datetime)
     * - area_recibe (varchar)
     * - recibe_nombre (varchar)
     * - firma_recepcion_path (varchar)
     * - recibido_por_id (fk users)
     */
    public function guardarRecepcion(Request $request, Requisicion $requisicion)
    {
        $this->authorize('receive', $requisicion);

        $data = $request->validate([
            'fecha_recibido' => ['required','date'],
            'area_recibe'    => ['required','string','max:255'],
            'recibe_nombre'  => ['required','string','max:255'],
            'firma_base64'   => ['required','string'],
        ]);

        if (!str_starts_with($data['firma_base64'], 'data:image/png;base64,')) {
            return back()
                ->withErrors(['firma_base64' => 'Por favor firma para registrar recepción.'])
                ->withInput();
        }

        $png  = base64_decode(Str::after($data['firma_base64'], 'data:image/png;base64,'));
        $path = "firmas/recepciones/req_{$requisicion->id}/recepcion_" . now()->format('Ymd_His') . ".png";
        Storage::disk('public')->put($path, $png);

        $requisicion->update([
            'fecha_recibido'        => $data['fecha_recibido'],
            'area_recibe'           => $data['area_recibe'],
            'recibe_nombre'         => $data['recibe_nombre'],
            'firma_recepcion_path'  => $path,
            'recibido_por_id'       => auth()->id(),
            'estado'                => 'recibida',
        ]);

        return redirect()->route('requisiciones.index')
            ->with('status', 'Recepción registrada correctamente.');
    }

        public function pdf(Requisicion $requisicion)
    {
        $this->authorize('view', $requisicion);
 
        // Permitir PDF cuando está aprobada, pendiente de cierre o recibida
        abort_unless(
            in_array($requisicion->estado, ['aprobada_final', 'pendiente_cierre', 'recibida'], true),
            403,
            'No se puede generar PDF de una requisición que no ha sido aprobada.'
        );
 
        $requisicion->load([
            'solicitante:id,name',
            'departamentoRef:id,nombre',
            'centroCostoRef:id,nombre',
            'items'               => fn($q) => $q->orderBy('id'),
            'items.unidadMedida',          // ← nuevo: unidad del catálogo
            'items.retenciones.tipoRetencion',
            'items.tipoImpuesto',          // ← impuesto 1
            'items.tipoImpuesto2',         // ← impuesto 2
            'aprobaciones.nivel',
            'aprobaciones.aprobador',
        ]);
 
        // Firmas de aprobaciones → data URI
        $requisicion->aprobaciones->each(function ($ap) {
            $ap->firma_data_uri = null;
            if (!empty($ap->firma_path)) {
                $full = Storage::disk('public')->path($ap->firma_path);
                if (is_file($full)) {
                    $ap->firma_data_uri = 'data:image/png;base64,'
                        . base64_encode(file_get_contents($full));
                }
            }
        });
 
        // Firma de recepción → base64
        $firmaRecepcionBase64 = null;
        if (!empty($requisicion->firma_recepcion_path)) {
            $full = Storage::disk('public')->path($requisicion->firma_recepcion_path);
            if (is_file($full)) {
                $firmaRecepcionBase64 = 'data:image/png;base64,'
                    . base64_encode(file_get_contents($full));
            }
        }
 
        // Totales (usa los campos guardados en BD, no recalcula)
        $subtotal = (float) ($requisicion->subtotal ?? 0);
        $iva      = (float) ($requisicion->iva      ?? 0);
        $total    = (float) ($requisicion->total    ?? 0);
        $ivaRate  = 0.16; // solo para compatibilidad con la vista
 
        // Logo
        $logoBase64 = null;
        $logoPath   = public_path('images/logo.png');
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }
 
        return Pdf::loadView('requisiciones.pdf', compact(
                'requisicion',
                'subtotal', 'iva', 'total', 'ivaRate',
                'logoBase64',
                'firmaRecepcionBase64'
            ))
            ->setPaper('a4', 'portrait')
            ->stream("REQ-{$requisicion->folio}.pdf");
    }

    public function duplicar(Requisicion $requisicion)
    {
        abort_unless(
            in_array($requisicion->estado, ['rechazada', 'recibida', 'rechazada_compras']),
            403,
            'Solo se pueden duplicar requisiciones rechazadas o recibidas.'
        );
 
        $requisicion->load(['items.tiposRetencion']);
 
        $nueva = null;
 
        DB::transaction(function () use ($requisicion, &$nueva) {
            $nueva = Requisicion::create([
                'folio'           => $this->generarFolioRequisicion(),
                'solicitante_id'  => Auth::id(),
                'departamento_id' => $requisicion->departamento_id,
                'centro_costo_id' => $requisicion->centro_costo_id,
                'justificacion'   => $requisicion->justificacion,
                'es_pago_factura' => $requisicion->es_pago_factura,
                'urgencia'        => $requisicion->urgencia,
                'estado'          => 'borrador',
                'fecha_emision'   => now(),
                'subtotal'        => $requisicion->subtotal,
                'iva'             => $requisicion->iva,
                'total'           => $requisicion->total,
                // NO se copia: metodo_pago, factura_path, uuid, archivos de cierre
            ]);
 
            foreach ($requisicion->items as $item) {
                $nuevoItem = \App\Models\RequisicionItem::create([
                    'requisicion_id'   => $nueva->id,
                    'descripcion'      => $item->descripcion,
                    'unidad_medida_id' => $item->unidad_medida_id,
                    'unidad'           => $item->unidad,
                    'cantidad'         => $item->cantidad,
                    'precio_unitario'  => $item->precio_unitario,
                    'subtotal'         => $item->subtotal,
                    'tipo_impuesto_id' => $item->tipo_impuesto_id,
                    'monto_impuesto'   => $item->monto_impuesto,
                    'total_item'       => $item->total_item,
                    'link_compra'      => $item->link_compra,
                    'proveedor_sugerido' => $item->proveedor_sugerido,
                    'monto_retenciones'  => $item->monto_retenciones,
                    'total_neto'         => $item->total_neto,
                    // NO se copia: metodo_pago (lo asigna Compras), ficha_tecnica_path/nombre
                    // NO se copian archivos adjuntos (RequisicionItemArchivo)
                ]);
 
                // Copiar retenciones (son referencias a catálogo, no archivos)
                foreach ($item->tiposRetencion as $retencion) {
                    $montoOriginal = \App\Models\RequisicionItemRetencion::where([
                        'requisicion_item_id' => $item->id,
                        'tipo_retencion_id'   => $retencion->id,
                    ])->value('monto') ?? 0;
 
                    \App\Models\RequisicionItemRetencion::create([
                        'requisicion_item_id' => $nuevoItem->id,
                        'tipo_retencion_id'   => $retencion->id,
                        'monto'               => $montoOriginal,
                    ]);
                }
            }
        });
 
        return redirect()
            ->route('requisiciones.edit', $nueva)
            ->with('status', "Copia creada de {$requisicion->folio}. Revisa los datos y envía cuando esté lista.");
    }

    private function generarFolioRequisicion(): string
{
    $fecha = now();

    // Ejemplo: REQ-2605
    $prefijo = 'REQ-' . $fecha->format('ym');

    $ultimo = Requisicion::where('folio', 'like', $prefijo . '-%')
        ->lockForUpdate()
        ->orderByDesc('id')
        ->first();

    if ($ultimo && preg_match('/-(\d+)$/', $ultimo->folio, $matches)) {
        $consecutivo = (int) $matches[1] + 1;
    } else {
        $consecutivo = 1;
    }

    return $prefijo . '-' . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
}

public function guardarOcNetsuite(Request $request, Requisicion $requisicion)
    {
        abort_unless(
            auth()->user()->hasAnyRole(['compras', 'administrador']),
            403
        );

        abort_unless(
            in_array($requisicion->estado, ['aprobada_final', 'pendiente_cierre', 'recibida']),
            403,
            'No se puede registrar OC en este estado.'
        );

        $data = $request->validate([
            'oc_netsuite' => ['nullable', 'string', 'max:100'],
        ]);

        $requisicion->update(['oc_netsuite' => $data['oc_netsuite'] ?? null]);

        return redirect()
            ->route('requisiciones.show', $requisicion)
            ->with('oc_guardado', true);
    }
}
