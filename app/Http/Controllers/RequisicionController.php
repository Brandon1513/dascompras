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
        $estado      = $request->query('estado');
        $solicitante = $request->query('solicitante');
        $user        = auth()->user();

        $requisiciones = Requisicion::with([
                'solicitante',
                'departamentoRef',
                'centroCostoRef',
                'aprobaciones.nivel',
                'aprobaciones.aprobador',
            ])
            ->visibleTo($user)
            ->when($estado, fn($q) => $q->where('estado', $estado))
            ->when($solicitante, fn($q) => $q->where('solicitante_id', $solicitante))
            ->latest('id')
            ->paginate(15)
            ->appends($request->query());

        $solicitantes = User::orderBy('name')->get(['id','name']);

        return view('requisiciones.index', compact('requisiciones','solicitantes','estado','solicitante','user'));
    }

    public function create()
    {
        return view('requisiciones.create');
    }

    public function edit(Requisicion $requisicion)
    {
        $this->authorize('update', $requisicion);
        return view('requisiciones.edit', compact('requisicion'));
    }

    public function show(Requisicion $requisicion)
    {
        $this->authorize('view', $requisicion);

        $requisicion->load([
            'solicitante',
            'departamentoRef',
            'centroCostoRef',
            'items',
            'aprobaciones.nivel',
            'aprobaciones.aprobador'
        ]);

        $puedeFirmar = auth()->user()->can('approve', $requisicion);

        return view('requisiciones.show', compact('requisicion','puedeFirmar'));
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

        abort_unless(
            in_array($requisicion->estado, ['aprobada_final','recibida'], true),
            403,
            'No se puede generar PDF de una requisición que no ha sido aprobada.'
        );

        $requisicion->load([
            'solicitante:id,name',
            'departamentoRef:id,nombre',
            'centroCostoRef:id,nombre',
            'items' => fn ($q) => $q->orderBy('id'),
            'aprobaciones.nivel',
            'aprobaciones.aprobador',
        ]);

        // ✅ Firmas de aprobaciones (firma_path -> data uri)
        $requisicion->aprobaciones->each(function ($ap) {
            $ap->firma_data_uri = null;

            if (!empty($ap->firma_path)) {
                $full = Storage::disk('public')->path($ap->firma_path);
                if (is_file($full)) {
                    $ap->firma_data_uri = 'data:image/png;base64,' . base64_encode(file_get_contents($full));
                }
            }
        });

        // ✅ Firma de recepción (firma_recepcion_path -> base64)
        $firmaRecepcionBase64 = null;
        if (!empty($requisicion->firma_recepcion_path)) {
            $full = Storage::disk('public')->path($requisicion->firma_recepcion_path);
            if (is_file($full)) {
                $firmaRecepcionBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($full));
            }
        }

        $ivaRate = 0.16;

        $subtotalItems = $requisicion->items->sum(function ($it) {
            if (!is_null($it->subtotal)) return (float) $it->subtotal;
            $cant = (float) ($it->cantidad ?? 0);
            $pu   = (float) ($it->precio_unitario ?? 0);
            return $cant * $pu;
        });

        $subtotal = !is_null($requisicion->subtotal) ? (float) $requisicion->subtotal : round($subtotalItems, 2);

        $aplicaIva = data_get($requisicion, 'aplica_iva', true);
        $iva   = !is_null($requisicion->iva) ? (float) $requisicion->iva : ($aplicaIva ? round($subtotal * $ivaRate, 2) : 0);
        $total = !is_null($requisicion->total) ? (float) $requisicion->total : round($subtotal + $iva, 2);

        //Logo de la empresa
        $logoBase64 = null;
        $logoPath = public_path('images/logo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        return Pdf::loadView('requisiciones.pdf', compact(
                'requisicion','subtotal','iva','total','ivaRate','logoBase64','firmaRecepcionBase64'
            ))
            ->setPaper('a4', 'portrait')
            ->stream("REQ-{$requisicion->folio}.pdf");
    }
}
