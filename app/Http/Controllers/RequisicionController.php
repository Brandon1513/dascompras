<?php

namespace App\Http\Controllers;

use App\Models\Requisicion;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $requisicion->load(['solicitante','departamentoRef','centroCostoRef','items']);

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

}
