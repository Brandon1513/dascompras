<?php
// ══════════════════════════════════════════════════════════════
// Ejecuta en tinker para crear los 3 tipos de retención iniciales:
//
//   php artisan tinker
//
//   $tipos = [
//       ['nombre' => 'ISR 1.25%',        'clave' => 'ISR_125',    'porcentaje' => 1.25],
//       ['nombre' => 'IVA Retenido 10.667%', 'clave' => 'IVA_RET_10667', 'porcentaje' => 10.667],
//       ['nombre' => 'ISR 10%',           'clave' => 'ISR_10',     'porcentaje' => 10.0],
//   ];
//   foreach ($tipos as $t) {
//       App\Models\TipoRetencion::firstOrCreate(['clave' => $t['clave']], array_merge($t, ['activo' => true]));
//   }
//
// ══════════════════════════════════════════════════════════════
// O como seeder formal en database/seeders/TiposRetencionSeeder.php:

namespace Database\Seeders;

use App\Models\TipoRetencion;
use Illuminate\Database\Seeder;

class TiposRetencionSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            [
                'nombre'     => 'ISR 1.25%',
                'clave'      => 'ISR_125',
                'porcentaje' => 1.25,
                'activo'     => true,
            ],
            [
                'nombre'     => 'IVA Retenido 10.667%',
                'clave'      => 'IVA_RET_10667',
                'porcentaje' => 10.667,
                'activo'     => true,
            ],
            [
                'nombre'     => 'ISR 10%',
                'clave'      => 'ISR_10',
                'porcentaje' => 10.0,
                'activo'     => true,
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoRetencion::firstOrCreate(
                ['clave' => $tipo['clave']],
                $tipo
            );
        }

        $this->command->info('✅ Tipos de retención creados.');
    }
}