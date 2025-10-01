<?php

namespace App\Http\Controllers;

use App\Models\Requisicion;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

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
            'aprobaciones.nivel',
            'aprobaciones.aprobador',
        ])
        ->visibleTo($user) // 👈 filtro por rol
        ->when($estado, fn($q) => $q->where('estado', $estado))
        // Nota: permitir filtrar por solicitante fue útil para admins/aprobadores.
        // Para empleado/jefe no hace falta, pero lo dejamos; el scope ya restringe resultados.
        ->when($solicitante, fn($q) => $q->where('solicitante_id', $solicitante))
        ->latest('id')
        ->paginate(15)
        ->appends($request->query());

    $solicitantes = \App\Models\User::orderBy('name')->get(['id','name']);

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

    $requisicion->load(['solicitante','departamentoRef','centroCostoRef','items','aprobaciones.nivel','aprobaciones.aprobador']);

    $puedeFirmar = auth()->user()->can('approve', $requisicion);

    return view('requisiciones.show', compact('requisicion','puedeFirmar'));
}
    // página de registrar recepción
    public function recibir(Requisicion $requisicion)
    {
        $this->authorize('receive', $requisicion);
        return view('requisiciones.recibir', compact('requisicion'));
    }
}
