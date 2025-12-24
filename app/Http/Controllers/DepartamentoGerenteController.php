<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\User;
use Illuminate\Http\Request;

class DepartamentoGerenteController extends Controller
{
    public function index()
    {
        $departamentos = Departamento::query()
            ->orderBy('nombre')
            ->with('gerente:id,name')
            ->get();

        return view('departamentos.gerentes.index', compact('departamentos'));
    }

    public function edit(Departamento $departamento)
    {
        $departamento->load('gerente:id,name');

        // ✅ Como puede ser jefe o gerente, NO filtres demasiado.
        // Si quieres filtrar, puedes dejar: ->role(['jefe','gerente_area'])->get()
        $usuarios = User::query()
            ->orderBy('name')
            ->get(['id','name']);

        return view('departamentos.gerentes.edit', compact('departamento','usuarios'));
    }

    public function update(Request $request, Departamento $departamento)
    {
        $data = $request->validate([
            'gerente_id' => ['nullable','exists:users,id'],
        ]);

        // ✅ Aquí decides si permites que un usuario sea gerente de varios deptos.
        // Si NO lo quieres permitir, descomenta esto:
        /*
        if (!empty($data['gerente_id'])) {
            $ya = Departamento::where('gerente_id', $data['gerente_id'])
                ->where('id', '!=', $departamento->id)
                ->first();

            if ($ya) {
                return back()->withErrors([
                    'gerente_id' => "Ese usuario ya es gerente de: {$ya->nombre}"
                ])->withInput();
            }
        }
        */

        $departamento->update([
            'gerente_id' => $data['gerente_id'] ?? null,
        ]);

        return redirect()
            ->route('departamentos.gerentes.index')
            ->with('status', 'Gerente asignado correctamente.');
    }
}
