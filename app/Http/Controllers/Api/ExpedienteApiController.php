<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expediente;
use Illuminate\Http\Request;

class ExpedienteApiController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));
        $estado = $request->get('estado');

        $expedientes = Expediente::query()
            ->when($q, fn($qr)=>$qr->where('nombre_carpeta','like',"%{$q}%"))
            ->when($estado, fn($qr)=>$qr->where('estado',$estado))
            ->latest()
            ->paginate(15); // si prefieres array simple: ->take(50)->get()

        return response()->json($expedientes);
    }

    public function show(Expediente $expediente)
    {
        $expediente->load('archivos:expediente_id,tipo,nombre_original,web_url,tamano,created_at', 'creador:id,name');
        return response()->json($expediente);
    }
}
