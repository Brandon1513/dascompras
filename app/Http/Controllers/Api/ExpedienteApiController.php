<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expediente;
use Illuminate\Http\Request;

class ExpedienteApiController extends Controller
{
    public function index(Request $request)
    {
        $q       = trim((string) $request->get('q'));
        $estado  = $request->get('estado');   // 'completo' | 'incompleto'
        $manual  = $request->get('manual');   // '1' | '0' | null

        $expedientes = Expediente::query()
            // búsqueda por nombre de carpeta
            ->when($q, fn ($qr) => $qr->where('nombre_carpeta', 'like', "%{$q}%"))

            // filtro por estado si lo envías (completo/incompleto)
            ->when($estado, fn ($qr) => $qr->where('estado', $estado))

            // filtro por "completado manual"
            // si manual = '1' -> solo los completados manualmente
            // si manual = '0' -> los NO completados manualmente
            ->when($manual !== null && $manual !== '', function ($qr) use ($manual) {
                if ($manual === '1') {
                    $qr->where('completado_manual', true);
                } else {
                    $qr->where(function ($qz) {
                        $qz->whereNull('completado_manual')
                           ->orWhere('completado_manual', false);
                    });
                }
            })

            ->latest()
            ->paginate(15);

        return response()->json($expedientes);
    }

    public function show(Expediente $expediente)
    {
        $expediente->load(
            'archivos:expediente_id,tipo,nombre_original,web_url,tamano,created_at',
            'creador:id,name'
        );
        return response()->json($expediente);
    }
}
