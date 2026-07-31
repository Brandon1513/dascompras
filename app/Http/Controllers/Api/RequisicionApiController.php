<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Requisicion;
use Illuminate\Http\Request;

class RequisicionApiController extends Controller
{
    /**
     * GET /api/requisiciones?desde=YYYY-MM-DD&hasta=YYYY-MM-DD
     * Expone las requisiciones para que dasavena-api pueda cruzarlas
     * con las Órdenes de Compra de NetSuite (KPI: Días de colocación de OC).
     */
    public function index(Request $request)
    {
        $query = Requisicion::query()->with('solicitante:id,name');

        if ($request->filled('desde')) {
            $query->whereDate('fecha_emision', '>=', $request->query('desde'));
        }
        if ($request->filled('hasta')) {
            $query->whereDate('fecha_emision', '<=', $request->query('hasta'));
        }

        $requisiciones = $query->orderByDesc('fecha_emision')->get();

        $data = $requisiciones->map(function (Requisicion $r) {
            return [
                'folio'         => $r->folio,
                'fecha_emision' => optional($r->fecha_emision)->format('Y-m-d'),
                'solicitante'   => $r->solicitante->name ?? null,
                'departamento'  => $r->departamento,
                'estado'        => $r->estado_label,
                'tipo'          => $r->es_pago_factura ? 'Pago de factura' : 'Requisición',
                'urgencia'      => $r->urgencia,
                'metodo_pago'   => $r->metodo_pago_label,
                'tiene_factura' => (bool) $r->tiene_factura,
                'uuid_factura'  => $r->uuid_factura,
                'oc_netsuite'   => $r->oc_netsuite,
            ];
        });

        return response()->json([
            'ok'    => true,
            'total' => $data->count(),
            'data'  => $data,
        ]);
    }
}