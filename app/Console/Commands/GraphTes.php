<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GraphSharePointService;

class GraphTes extends Command
{
    protected $signature = 'graph:test {folder?}';
    protected $description = 'Crea una carpeta en SharePoint y devuelve su enlace';

    public function handle(GraphSharePointService $graph)
    {
        $driveId    = env('GRAPH_DRIVE_ID');
        $rootFolder = trim(env('GRAPH_FOLDER_ROOT', '/Expedientes'), '/'); // ej. "Expedientes"
        $folderName = $this->argument('folder') ?: 'TEST-'.now()->format('Ymd-His');

        // IMPORTANTE: Asume que la carpeta raíz (Expedientes) YA existe en la biblioteca.
        // Si no existe, créala primero en SharePoint.

        $parentPath = "/{$rootFolder}"; // /Expedientes
        $created    = $graph->createFolder($driveId, $parentPath, $folderName);
        $link       = $graph->createFolderLink($driveId, $created['id']);

        $this->info("Carpeta: {$created['name']}");
        $this->line("Link: {$link}");

        return self::SUCCESS;
    }
}
