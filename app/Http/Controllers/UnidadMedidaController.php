<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class UnidadMedidaController extends Controller
{
    public function index()
    {
        $unidades = UnidadMedida::orderBy('nombre')->paginate(20);
        return view('catalogos.unidades.index', compact('unidades'));
    }

    public function create()
    {
        return view('catalogos.unidades.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:100', 'unique:unidades_medida,nombre'],
            'abreviatura' => ['required', 'string', 'max:20',
                              'unique:unidades_medida,abreviatura'],
            'activo'      => ['boolean'],
        ]);

        $data['abreviatura'] = strtoupper($data['abreviatura']);
        $data['activo']      = $request->boolean('activo', true);

        UnidadMedida::create($data);

        return redirect()->route('catalogos.unidades.index')
            ->with('success', 'Unidad de medida creada correctamente.');
    }

    public function edit(UnidadMedida $unidad)
    {
        return view('catalogos.unidades.edit', compact('unidad'));
    }

    public function update(Request $request, UnidadMedida $unidad)
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:100',
                              "unique:unidades_medida,nombre,{$unidad->id}"],
            'abreviatura' => ['required', 'string', 'max:20',
                              "unique:unidades_medida,abreviatura,{$unidad->id}"],
            'activo'      => ['boolean'],
        ]);

        $data['abreviatura'] = strtoupper($data['abreviatura']);
        $data['activo']      = $request->boolean('activo');

        $unidad->update($data);

        return redirect()->route('catalogos.unidades.index')
            ->with('success', 'Unidad de medida actualizada correctamente.');
    }

    public function toggle(UnidadMedida $unidad)
    {
        $unidad->update(['activo' => !$unidad->activo]);

        $msg = $unidad->activo ? 'activada' : 'desactivada';
        return back()->with('success', "Unidad \"{$unidad->nombre}\" {$msg}.");
    }
}