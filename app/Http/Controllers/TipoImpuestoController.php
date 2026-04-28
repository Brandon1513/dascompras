<?php

namespace App\Http\Controllers;

use App\Models\TipoImpuesto;
use Illuminate\Http\Request;

class TipoImpuestoController extends Controller
{
    public function index()
    {
        $impuestos = TipoImpuesto::orderBy('nombre')->paginate(20);
        return view('catalogos.impuestos.index', compact('impuestos'));
    }

    public function create()
    {
        return view('catalogos.impuestos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:100', 'unique:tipos_impuesto,nombre'],
            'clave'       => ['required', 'string', 'max:30',
                              'unique:tipos_impuesto,clave', 'regex:/^[A-Z0-9_]+$/'],
            'porcentaje'  => ['required', 'numeric', 'min:0', 'max:100'],
            'activo'      => ['boolean'],
        ], [
            'clave.regex' => 'La clave solo puede contener letras mayúsculas, números y guion bajo.',
        ]);

        $data['clave']  = strtoupper($data['clave']);
        $data['activo'] = $request->boolean('activo', true);

        TipoImpuesto::create($data);

        return redirect()->route('catalogos.impuestos.index')
            ->with('success', 'Tipo de impuesto creado correctamente.');
    }

    public function edit(TipoImpuesto $impuesto)
    {
        return view('catalogos.impuestos.edit', compact('impuesto'));
    }

    public function update(Request $request, TipoImpuesto $impuesto)
    {
        $data = $request->validate([
            'nombre'     => ['required', 'string', 'max:100',
                             "unique:tipos_impuesto,nombre,{$impuesto->id}"],
            'clave'      => ['required', 'string', 'max:30',
                             "unique:tipos_impuesto,clave,{$impuesto->id}",
                             'regex:/^[A-Z0-9_]+$/'],
            'porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'activo'     => ['boolean'],
        ], [
            'clave.regex' => 'La clave solo puede contener letras mayúsculas, números y guion bajo.',
        ]);

        $data['clave']  = strtoupper($data['clave']);
        $data['activo'] = $request->boolean('activo');

        $impuesto->update($data);

        return redirect()->route('catalogos.impuestos.index')
            ->with('success', 'Tipo de impuesto actualizado correctamente.');
    }

    public function toggle(TipoImpuesto $impuesto)
    {
        $impuesto->update(['activo' => !$impuesto->activo]);

        $msg = $impuesto->activo ? 'activado' : 'desactivado';
        return back()->with('success', "Impuesto \"{$impuesto->nombre}\" {$msg}.");
    }
}