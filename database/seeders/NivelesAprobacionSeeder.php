<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NivelesAprobacionSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['nombre'=>'Jefe directo','monto_min'=>0,'monto_max'=>1000,'rol_aprobador'=>'jefe_directo','orden'=>1],
            ['nombre'=>'Gerencia de área','monto_min'=>1000.01,'monto_max'=>5000,'rol_aprobador'=>'gerente_area','orden'=>2],
            ['nombre'=>'Gerencia Administrativa','monto_min'=>5000.01,'monto_max'=>25000,'rol_aprobador'=>'gerencia_adm','orden'=>3],
            ['nombre'=>'Dirección','monto_min'=>25000.01,'monto_max'=>null,'rol_aprobador'=>'direccion','orden'=>4],
        ];

        foreach ($rows as $r) {
            DB::table('niveles_aprobacion')->updateOrInsert(
                ['nombre' => $r['nombre']],
                $r
            );
        }
    }
}
