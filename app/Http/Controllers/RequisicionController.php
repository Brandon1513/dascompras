<?php

namespace App\Http\Controllers;

use App\Models\Requisicion;
use App\Models\User;
use Illuminate\Http\Request;

class RequisicionController extends Controller
{
    public function index(Request $request)
    {
        $estado       = $request->query('estado');        // borrador, enviada, ...
        $solicitante  = $request->query('solicitante');   // user_id

        $requisiciones = Requisicion::with(['solicitante','departamentoRef'])
            ->when($estado, fn($q) => $q->where('estado', $estado))
            ->when($solicitante, fn($q) => $q->where('solicitante_id', $solicitante))
            ->latest('id')
            ->paginate(15)
            ->appends($request->query()); // conserva filtros en la paginación

        $solicitantes = User::orderBy('name')->get(['id','name']);

        return view('requisiciones.index', compact('requisiciones','solicitantes','estado','solicitante'));
    }

    public function create()
    {
        return view('requisiciones.create');
    }

    public function edit(Requisicion $requisicion)
    {
        // La edición solo para el dueño y cuando esté en borrador
        abort_unless($requisicion->solicitante_id === auth()->id() && $requisicion->estado === 'borrador', 403);

        return view('requisiciones.edit', compact('requisicion'));
    }
}
