<?php

namespace App\Http\Controllers;

use App\Models\Expediente;
use Illuminate\Http\Request;
use App\Models\ExpedienteArchivo;
use App\Services\GraphSharePointService;
use App\Http\Requests\AdjuntarArchivoRequest;
use App\Http\Requests\CargaExpedienteRequest; // la misma FormRequest del API

class ExpedienteWebController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));
        $estado = $request->get('estado');

        $expedientes = Expediente::query()
            ->when($q, fn($qr)=>$qr->where('nombre_carpeta','like',"%{$q}%"))
            ->when($estado, fn($qr)=>$qr->where('estado',$estado))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('expedientes.index', compact('expedientes','q','estado'));
    }

    public function show(Expediente $expediente)
    {
        $expediente->load('archivos','creador');
        return view('expedientes.show', compact('expediente'));
    }
    public function create()
    {
        return view('expedientes.carga'); // la vista web
    }

    public function store(CargaExpedienteRequest $request, GraphSharePointService $graph)
{
    $data     = $request->validated();
    $userId   = $request->user()->id;
    $driveId  = env('GRAPH_DRIVE_ID');
    $root     = trim(env('GRAPH_FOLDER_ROOT','/Expedientes'), '/');

    // 1) Nombre de carpeta saneado
    $folderName = $this->sanitizeFolderName($data['carpeta']);

    // 2) Asegura raíz
    try { $graph->createFolder($driveId, '/', $root); } catch (\Throwable $e) {}

    // 3) Crea carpeta del expediente + link
    $folder     = $graph->createFolder($driveId, "/{$root}", $folderName);
    $folderLink = $graph->createFolderLink($driveId, $folder['id']);

    // 4) Rutas y subcarpetas
    $basePath    = "/{$root}/{$folder['name']}";
    $requiPath   = "{$basePath}/01_REQUI";
    $facturaPath = "{$basePath}/02_FACTURA";
    $otrosPath   = "{$basePath}/03_OTROS";

    $graph->createFolder($driveId, $basePath, "01_REQUI");
    $graph->createFolder($driveId, $basePath, "02_FACTURA");
    $graph->createFolder($driveId, $basePath, "03_OTROS");

    // 5) Crea expediente en BD
    $exp = Expediente::create([
        'nombre_carpeta' => $folder['name'],     // por si Graph renombró
        'folder_item_id' => $folder['id'] ?? null,
        'folder_link'    => $folderLink,
        'drive_id'       => $driveId,
        'folder_path'    => $basePath,           // "/Expedientes/EXP-..."
        'created_by'     => $userId,
    ]);

    // 6) Subir archivos + registrar en BD
    $hasRequi = false; $hasFactura = false; $otrosCount = 0; $subidos = [];

    if ($request->hasFile('requi')) {
        $f = $request->file('requi');
        $name = time().'_REQUI_'.$f->getClientOriginalName();
        $it = $graph->upload($driveId, $requiPath, $name, $f->getRealPath());

        ExpedienteArchivo::create([
            'expediente_id'   => $exp->id,
            'tipo'            => 'requi',
            'nombre_original' => $f->getClientOriginalName(),
            'extension'       => $f->getClientOriginalExtension(),
            'tamano'          => $f->getSize(),
            'item_id'         => $it['id'] ?? null,
            'web_url'         => $it['webUrl'] ?? null,
            'subido_por'      => $userId,
        ]);

        $hasRequi = true;
        $subidos[] = ['tipo'=>'Requisición','nombre'=>$f->getClientOriginalName(),'url'=>$it['webUrl'] ?? null];
    }

    if ($request->hasFile('factura')) {
        $f = $request->file('factura');
        $name = time().'_FACTURA_'.$f->getClientOriginalName();
        $it = $graph->upload($driveId, $facturaPath, $name, $f->getRealPath());

        ExpedienteArchivo::create([
            'expediente_id'   => $exp->id,
            'tipo'            => 'factura',
            'nombre_original' => $f->getClientOriginalName(),
            'extension'       => $f->getClientOriginalExtension(),
            'tamano'          => $f->getSize(),
            'item_id'         => $it['id'] ?? null,
            'web_url'         => $it['webUrl'] ?? null,
            'subido_por'      => $userId,
        ]);

        $hasFactura = true;
        $subidos[] = ['tipo'=>'Factura','nombre'=>$f->getClientOriginalName(),'url'=>$it['webUrl'] ?? null];
    }

    foreach ($request->file('otros', []) as $i => $f) {
        $name = time()."_OTRO{$i}_".$f->getClientOriginalName();
        $it = $graph->upload($driveId, $otrosPath, $name, $f->getRealPath());

        ExpedienteArchivo::create([
            'expediente_id'   => $exp->id,
            'tipo'            => 'otros',
            'nombre_original' => $f->getClientOriginalName(),
            'extension'       => $f->getClientOriginalExtension(),
            'tamano'          => $f->getSize(),
            'item_id'         => $it['id'] ?? null,
            'web_url'         => $it['webUrl'] ?? null,
            'subido_por'      => $userId,
        ]);

        $otrosCount++;
        $subidos[] = ['tipo'=>'Otro','nombre'=>$f->getClientOriginalName(),'url'=>$it['webUrl'] ?? null];
    }

    // 7) Actualiza flags/estado
    $exp->update([
        'has_requi'   => $hasRequi,
        'has_factura' => $hasFactura,
        'otros_count' => $otrosCount,
        'estado'      => ($hasRequi && $hasFactura && $otrosCount > 0) ? 'completo' : 'incompleto',
    ]);

    // 8) Respuesta
    return redirect()
        ->route('expedientes.index')
        ->with('success', "Expediente creado y archivos subidos.")
        ->with('link', $folderLink)
        ->with('folder_name', $folder['name'])
        ->with('subidos', $subidos);
}


    private function sanitizeFolderName(string $name): string
    {
        $name = preg_replace('/["*:<>?\/\\\\|#%]/', '-', $name);
        return trim($name, ". ") ?: 'Carpeta';
    }
    public function edit(Expediente $expediente)
{
    $expediente->load('archivos');
    return view('expedientes.adjuntar', compact('expediente'));
}

// Adjunta archivos faltantes al expediente existente
public function attach(AdjuntarArchivoRequest $request, Expediente $expediente, GraphSharePointService $graph)
    {
        $userId  = $request->user()->id;
        $driveId = $expediente->drive_id ?? env('GRAPH_DRIVE_ID');

        // Rutas de subcarpetas (usa folder_path guardado al crear)
        $basePath    = $expediente->folder_path
                    ?: '/'.trim(env('GRAPH_FOLDER_ROOT','/Expedientes'), '/').'/'.$expediente->nombre_carpeta;
        $requiPath   = "{$basePath}/01_REQUI";
        $facturaPath = "{$basePath}/02_FACTURA";
        $otrosPath   = "{$basePath}/03_OTROS";

        $hasRequi   = $expediente->has_requi;
        $hasFactura = $expediente->has_factura;
        $otrosCount = (int)$expediente->otros_count;

        // Subir y persistir
        if ($request->hasFile('requi')) {
            $f = $request->file('requi');
            $name = time().'_REQUI_'.$f->getClientOriginalName();
            $it = $graph->upload($driveId, $requiPath, $name, $f->getRealPath());

            ExpedienteArchivo::create([
                'expediente_id'   => $expediente->id,
                'tipo'            => 'requi',
                'nombre_original' => $f->getClientOriginalName(),
                'extension'       => $f->getClientOriginalExtension(),
                'tamano'          => $f->getSize(),
                'item_id'         => $it['id'] ?? null,
                'web_url'         => $it['webUrl'] ?? null,
                'subido_por'      => $userId,
            ]);
            $hasRequi = true;
        }

        if ($request->hasFile('factura')) {
            $f = $request->file('factura');
            $name = time().'_FACTURA_'.$f->getClientOriginalName();
            $it = $graph->upload($driveId, $facturaPath, $name, $f->getRealPath());

            ExpedienteArchivo::create([
                'expediente_id'   => $expediente->id,
                'tipo'            => 'factura',
                'nombre_original' => $f->getClientOriginalName(),
                'extension'       => $f->getClientOriginalExtension(),
                'tamano'          => $f->getSize(),
                'item_id'         => $it['id'] ?? null,
                'web_url'         => $it['webUrl'] ?? null,
                'subido_por'      => $userId,
            ]);
            $hasFactura = true;
        }

        foreach ($request->file('otros', []) as $i => $f) {
            $name = time()."_OTRO{$i}_".$f->getClientOriginalName();
            $it = $graph->upload($driveId, $otrosPath, $name, $f->getRealPath());

            ExpedienteArchivo::create([
                'expediente_id'   => $expediente->id,
                'tipo'            => 'otros',
                'nombre_original' => $f->getClientOriginalName(),
                'extension'       => $f->getClientOriginalExtension(),
                'tamano'          => $f->getSize(),
                'item_id'         => $it['id'] ?? null,
                'web_url'         => $it['webUrl'] ?? null,
                'subido_por'      => $userId,
            ]);
            $otrosCount++;
        }

        // Recalcular estado
        $expediente->update([
            'has_requi'   => $hasRequi,
            'has_factura' => $hasFactura,
            'otros_count' => $otrosCount,
            'estado'      => ($hasRequi && $hasFactura && $otrosCount > 0) ? 'completo' : 'incompleto',
        ]);

        return redirect()->route('expedientes.show', $expediente)
            ->with('success', 'Archivos adjuntados correctamente.');
    }
}
