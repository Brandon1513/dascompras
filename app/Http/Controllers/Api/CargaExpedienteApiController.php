<?php
// app/Http/Controllers/Api/CargaExpedienteApiController.php

namespace App\Http\Controllers\Api;

use App\Models\Expediente;
use App\Models\ExpedienteArchivo;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\GraphSharePointService;
use App\Http\Requests\AdjuntarArchivoRequest;
use App\Http\Requests\CargaExpedienteRequest;

class CargaExpedienteApiController extends Controller
{
    public function store(CargaExpedienteRequest $request, GraphSharePointService $graph)
    {
        $data    = $request->validated();
        $userId  = optional($request->user())->id; // debe venir por Sanctum
        $driveId = env('GRAPH_DRIVE_ID');
        $root    = trim(env('GRAPH_FOLDER_ROOT','/Expedientes'), '/');

        // Nombre de carpeta saneado para SharePoint
        $folderName = $this->sanitizeFolderName($data['carpeta']);

        try {
            // 1) Asegura carpeta raíz (idempotente)
            try { $graph->createFolder($driveId, '/', $root); } catch (\Throwable $e) {}

            // 2) Crea carpeta del expediente + link
            $folder     = $graph->createFolder($driveId, "/{$root}", $folderName);
            $folderLink = $graph->createFolderLink($driveId, $folder['id']);

            // 3) Rutas y subcarpetas
            $basePath    = "/{$root}/{$folder['name']}";
            $requiPath   = "{$basePath}/01_REQUI";
            $facturaPath = "{$basePath}/02_FACTURA";
            $otrosPath   = "{$basePath}/03_OTROS";

            $graph->createFolder($driveId, $basePath, "01_REQUI");
            $graph->createFolder($driveId, $basePath, "02_FACTURA");
            $graph->createFolder($driveId, $basePath, "03_OTROS");

            // 4) Persistir expediente en BD
            DB::beginTransaction();

            $exp = Expediente::create([
                'nombre_carpeta' => $folder['name'],   // por si Graph renombró por conflicto
                'folder_item_id' => $folder['id'] ?? null,
                'folder_link'    => $folderLink,
                'drive_id'       => $driveId,
                'folder_path'    => $basePath,
                'created_by'     => $userId,
            ]);

            // 5) Subir archivos y registrar en BD
            $hasRequi = false; $hasFactura = false; $otrosCount = 0;
            $items = [];

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
                $items[] = ['tipo'=>'requi','nombre'=>$f->getClientOriginalName(),'url'=>$it['webUrl'] ?? null];
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
                $items[] = ['tipo'=>'factura','nombre'=>$f->getClientOriginalName(),'url'=>$it['webUrl'] ?? null];
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
                $items[] = ['tipo'=>'otros','nombre'=>$f->getClientOriginalName(),'url'=>$it['webUrl'] ?? null];
            }

            // 6) Actualiza flags/estado
            $exp->update([
                'has_requi'   => $hasRequi,
                'has_factura' => $hasFactura,
                'otros_count' => $otrosCount,
                'estado'      => ($hasRequi && $hasFactura && $otrosCount > 0) ? 'completo' : 'incompleto',
            ]);

            DB::commit();

            // 7) Response
            $progreso = ((int)$hasRequi + (int)$hasFactura + ($otrosCount>0 ? 1 : 0)).'/3';

            return response()->json([
                'message' => 'Expediente creado y archivos subidos.',
                'expediente' => [
                    'id'             => $exp->id,
                    'nombre_carpeta' => $exp->nombre_carpeta,
                    'folder_link'    => $exp->folder_link,
                    'estado'         => $exp->estado,
                    'progreso'       => $progreso,
                    'created_by'     => $exp->created_by,
                ],
                'items' => $items,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'message' => 'Error al crear el expediente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function sanitizeFolderName(string $name): string
    {
        $name = preg_replace('/["*:<>?\/\\\\|#%]/', '-', $name);
        return trim($name, ". ") ?: 'Carpeta';
    }
    public function append(AdjuntarArchivoRequest $request, GraphSharePointService $graph, Expediente $expediente)
    {
        $userId  = optional($request->user())->id;
        $driveId = $expediente->drive_id ?? env('GRAPH_DRIVE_ID');

        $basePath    = $expediente->folder_path
            ?: '/'.trim(env('GRAPH_FOLDER_ROOT','/Expedientes'), '/').'/'.$expediente->nombre_carpeta;
        $requiPath   = "{$basePath}/01_REQUI";
        $facturaPath = "{$basePath}/02_FACTURA";
        $otrosPath   = "{$basePath}/03_OTROS";

        $hasRequi   = $expediente->has_requi;
        $hasFactura = $expediente->has_factura;
        $otrosCount = (int)$expediente->otros_count;
        $items = [];

        if ($request->hasFile('requi')) {
            $f = $request->file('requi');
            $name = time().'_REQUI_'.$f->getClientOriginalName();
            $it = $graph->upload($driveId, $requiPath, $name, $f->getRealPath());
            ExpedienteArchivo::create([
                'expediente_id'=>$expediente->id,'tipo'=>'requi',
                'nombre_original'=>$f->getClientOriginalName(),
                'extension'=>$f->getClientOriginalExtension(),
                'tamano'=>$f->getSize(),'item_id'=>$it['id'] ?? null,
                'web_url'=>$it['webUrl'] ?? null,'subido_por'=>$userId,
            ]);
            $hasRequi = true;
            $items[] = ['tipo'=>'requi','nombre'=>$f->getClientOriginalName(),'url'=>$it['webUrl'] ?? null];
        }

        if ($request->hasFile('factura')) {
            $f = $request->file('factura');
            $name = time().'_FACTURA_'.$f->getClientOriginalName();
            $it = $graph->upload($driveId, $facturaPath, $name, $f->getRealPath());
            ExpedienteArchivo::create([
                'expediente_id'=>$expediente->id,'tipo'=>'factura',
                'nombre_original'=>$f->getClientOriginalName(),
                'extension'=>$f->getClientOriginalExtension(),
                'tamano'=>$f->getSize(),'item_id'=>$it['id'] ?? null,
                'web_url'=>$it['webUrl'] ?? null,'subido_por'=>$userId,
            ]);
            $hasFactura = true;
            $items[] = ['tipo'=>'factura','nombre'=>$f->getClientOriginalName(),'url'=>$it['webUrl'] ?? null];
        }

        foreach ($request->file('otros', []) as $i => $f) {
            $name = time()."_OTRO{$i}_".$f->getClientOriginalName();
            $it = $graph->upload($driveId, $otrosPath, $name, $f->getRealPath());
            ExpedienteArchivo::create([
                'expediente_id'=>$expediente->id,'tipo'=>'otros',
                'nombre_original'=>$f->getClientOriginalName(),
                'extension'=>$f->getClientOriginalExtension(),
                'tamano'=>$f->getSize(),'item_id'=>$it['id'] ?? null,
                'web_url'=>$it['webUrl'] ?? null,'subido_por'=>$userId,
            ]);
            $otrosCount++;
            $items[] = ['tipo'=>'otros','nombre'=>$f->getClientOriginalName(),'url'=>$it['webUrl'] ?? null];
        }

        $expediente->update([
            'has_requi'   => $hasRequi,
            'has_factura' => $hasFactura,
            'otros_count' => $otrosCount,
            'estado'      => ($hasRequi && $hasFactura && $otrosCount > 0) ? 'completo' : 'incompleto',
        ]);

        return response()->json([
            'message' => 'Archivos adjuntados.',
            'estado'  => $expediente->estado,
            'progreso'=> ((int)$hasRequi + (int)$hasFactura + ($otrosCount>0 ? 1 : 0)).'/3',
            'items'   => $items,
        ]);
    }
}
