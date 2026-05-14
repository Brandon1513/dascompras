<?php

namespace App\Http\Controllers;

use App\Models\TipoRetencion;
use Illuminate\Http\Request;

class TipoRetencionController extends Controller
{
    public function index()
    {
        $retenciones = TipoRetencion::orderBy('nombre')->paginate(20);
        return view('catalogos.retenciones.index', compact('retenciones'));
    }

    public function create()
    {
        return view('catalogos.retenciones.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'     => ['required', 'string', 'max:100'],
            'clave'      => ['required', 'string', 'max:30', 'unique:tipos_retencion,clave',
                             'regex:/^[A-Z0-9_]+$/'],
            'porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'activo'     => ['boolean'],
        ], [
            'clave.regex' => 'Solo mayúsculas, números y guion bajo.',
            'clave.unique' => 'Ya existe una retención con esta clave.',
        ]);

        $data['activo'] = $request->boolean('activo', true);

        TipoRetencion::create($data);

        return redirect()
            ->route('catalogos.retenciones.index')
            ->with('success', 'Tipo de retención creado correctamente.');
    }

    public function edit(TipoRetencion $retencion)
    {
        return view('catalogos.retenciones.edit', compact('retencion'));
    }

    public function update(Request $request, TipoRetencion $retencion)
    {
        $data = $request->validate([
            'nombre'     => ['required', 'string', 'max:100'],
            'clave'      => ['required', 'string', 'max:30',
                             'unique:tipos_retencion,clave,' . $retencion->id,
                             'regex:/^[A-Z0-9_]+$/'],
            'porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'activo'     => ['boolean'],
        ], [
            'clave.regex' => 'Solo mayúsculas, números y guion bajo.',
        ]);

        $data['activo'] = $request->boolean('activo', true);

        $retencion->update($data);

        return redirect()
            ->route('catalogos.retenciones.index')
            ->with('success', 'Tipo de retención actualizado correctamente.');
    }

    public function toggle(TipoRetencion $retencion)
    {
        $retencion->update(['activo' => !$retencion->activo]);

        return back()->with('success',
            'Retención ' . ($retencion->activo ? 'activada' : 'desactivada') . ' correctamente.'
        );
    }
}