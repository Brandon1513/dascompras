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
    /* ======================= CREATE (desde la app) ======================= */
    public function store(CargaExpedienteRequest $request, GraphSharePointService $graph)
    {
        $data    = $request->validated();
        $userId  = optional($request->user())->id; // Sanctum
        $driveId = env('GRAPH_DRIVE_ID');
        $root    = trim(env('GRAPH_FOLDER_ROOT','/Expedientes'), '/');

        $folderName = $this->sanitizeFolderName($data['carpeta']);

        try {
            try { $graph->createFolder($driveId, '/', $root); } catch (\Throwable $e) {}

            // Carpeta expediente + link
            $folder     = $graph->createFolder($driveId, "/{$root}", $folderName);
            $folderLink = $graph->createFolderLink($driveId, $folder['id']);

            // Subcarpetas
            $basePath     = "/{$root}/{$folder['name']}";
            $requiPath    = "{$basePath}/01_REQUI";
            $facturaPath  = "{$basePath}/02_FACTURA";
            $recibosPath  = "{$basePath}/03_RECIBOS";

            $graph->createFolder($driveId, $basePath, "01_REQUI");
            $graph->createFolder($driveId, $basePath, "02_FACTURA");
            $graph->createFolder($driveId, $basePath, "03_RECIBOS");

            DB::beginTransaction();

            $exp = Expediente::create([
                'nombre_carpeta' => $folder['name'],
                'folder_item_id' => $folder['id'] ?? null,
                'folder_link'    => $folderLink,
                'drive_id'       => $driveId,
                'folder_path'    => $basePath,
                'created_by'     => $userId,
            ]);

            $hasRequi = false; $hasFactura = false; $otrosCount = 0;
            $items = [];

            // REQUIS
            foreach ((array) $request->file('requi', []) as $idx => $f) {
                if (!$f) continue;
                $name = time()."_REQUI{$idx}_".$f->getClientOriginalName();
                $it   = $graph->upload($driveId, $requiPath, $name, $f->getRealPath());

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

            // FACTURAS
            foreach ((array) $request->file('factura', []) as $idx => $f) {
                if (!$f) continue;
                $name = time()."_FACTURA{$idx}_".$f->getClientOriginalName();
                $it   = $graph->upload($driveId, $facturaPath, $name, $f->getRealPath());

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

            // RECIBOS (se guardan como tipo 'otros' para compatibilidad)
            foreach ((array) $request->file('recibos', []) as $idx => $f) {
                if (!$f) continue;
                $name = time()."_RECIBO{$idx}_".$f->getClientOriginalName();
                $it   = $graph->upload($driveId, $recibosPath, $name, $f->getRealPath());

                ExpedienteArchivo::create([
                    'expediente_id'   => $exp->id,
                    'tipo'            => 'otros', // <- mantener 'otros' en BD
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

            // Estado
            $exp->update([
                'has_requi'   => $hasRequi,
                'has_factura' => $hasFactura,
                'otros_count' => $otrosCount,
                'estado'      => ($hasRequi && $hasFactura && $otrosCount > 0) ? 'completo' : 'incompleto',
            ]);

            DB::commit();

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


    /* ======================= APPEND (agregar a existente) ======================= */
    public function append(AdjuntarArchivoRequest $request, GraphSharePointService $graph, Expediente $expediente)
    {
        $userId  = optional($request->user())->id;
        $driveId = $expediente->drive_id ?? env('GRAPH_DRIVE_ID');

        $basePath    = $expediente->folder_path
            ?: '/'.trim(env('GRAPH_FOLDER_ROOT','/Expedientes'), '/').'/'.$expediente->nombre_carpeta;

        $requiPath    = "{$basePath}/01_REQUI";
        $facturaPath  = "{$basePath}/02_FACTURA";
        $recibosPath  = "{$basePath}/03_RECIBOS"; // ← renombrada

        $hasRequi   = $expediente->has_requi;
        $hasFactura = $expediente->has_factura;
        $otrosCount = (int)$expediente->otros_count;
        $items = [];

        foreach ($this->filesFrom($request, 'requi') as $f) {
            $name = time().'_REQUI_'.$f->getClientOriginalName();
            $it   = $graph->upload($driveId, $requiPath, $name, $f->getRealPath());

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
            $items[]  = ['tipo'=>'requi','nombre'=>$f->getClientOriginalName(),'url'=>$it['webUrl'] ?? null];
        }

        foreach ($this->filesFrom($request, 'factura') as $f) {
            $name = time().'_FACTURA_'.$f->getClientOriginalName();
            $it   = $graph->upload($driveId, $facturaPath, $name, $f->getRealPath());

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
            $items[]    = ['tipo'=>'factura','nombre'=>$f->getClientOriginalName(),'url'=>$it['webUrl'] ?? null];
        }

        foreach ($this->filesFrom($request, 'recibos') as $i => $f) {
            $name = time()."_RECIBO{$i}_".$f->getClientOriginalName();
            $it   = $graph->upload($driveId, $recibosPath, $name, $f->getRealPath());

            ExpedienteArchivo::create([
                'expediente_id'   => $expediente->id,
                'tipo'            => 'otros', // mantenemos 'otros' en BD
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

    /* ======================= Helpers ======================= */

    private function sanitizeFolderName(string $name): string
    {
        $name = preg_replace('/["*:<>?\/\\\\|#%]/', '-', $name);
        return trim($name, ". ") ?: 'Carpeta';
    }

    /**
     * Normaliza: si llega 1 archivo -> array de 1; si llega 'campo[]' -> ese array
     */
    private function filesFrom($request, string $key): array
    {
        
        $files = $request->file($key);
        return $files ? (is_array($files) ? $files : [$files]) : [];
    }
}
