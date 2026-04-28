<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── id=1: Coordinación de Compras ────────────────────────────────
        // Pasa a ser el primer nivel del flujo (revisión inicial)
        DB::table('niveles_aprobacion')->where('id', 1)->update([
            'nombre'        => 'Coordinación de Compras',
            'orden'         => 1,
            'activo'        => true,
            'monto_min'     => 0.00,
            'monto_max'     => null,
            'rol_aprobador' => 'compras',
        ]);

        // ── id=5: Jefe directo ────────────────────────────────────────────
        // Segundo nivel, aplica para montos hasta $1,000
        DB::table('niveles_aprobacion')->where('id', 5)->update([
            'nombre'        => 'Jefe directo',
            'orden'         => 2,
            'activo'        => true,
            'monto_min'     => 0.00,
            'monto_max'     => 1000.00,
            'rol_aprobador' => 'jefe',
        ]);

        // ── id=2: Gerencia de Área → Gerente de Operaciones ──────────────
        // Tercer nivel, aplica para montos $1,001 - $5,000
        // El rol cambia de gerente_area → gerente_operaciones
        DB::table('niveles_aprobacion')->where('id', 2)->update([
            'nombre'        => 'Gerente de Operaciones',
            'orden'         => 3,
            'activo'        => true,
            'monto_min'     => 1001.00,
            'monto_max'     => 5000.00,
            'rol_aprobador' => 'gerente_operaciones',
        ]);

        // ── id=3: Gerencia Administrativa ────────────────────────────────
        // Cuarto nivel, aplica para montos > $5,000
        DB::table('niveles_aprobacion')->where('id', 3)->update([
            'nombre'        => 'Gerencia Administrativa',
            'orden'         => 4,
            'activo'        => true,
            'monto_min'     => 5001.00,
            'monto_max'     => null,
            'rol_aprobador' => 'gerencia_adm',
        ]);

        // ── id=4: Dirección ──────────────────────────────────────────────
        // Desactivar — no aplica en el nuevo flujo
        DB::table('niveles_aprobacion')->where('id', 4)->update([
            'activo' => false,
        ]);
    }

    public function down(): void
    {
        // Restaurar valores originales
        DB::table('niveles_aprobacion')->where('id', 1)->update([
            'orden' => 4, 'activo' => false, 'monto_min' => 0, 'monto_max' => 1000,
        ]);
        DB::table('niveles_aprobacion')->where('id', 5)->update([
            'orden' => 1, 'monto_min' => 0, 'monto_max' => 0,
        ]);
        DB::table('niveles_aprobacion')->where('id', 2)->update([
            'nombre' => 'Gerencia de Área', 'orden' => 2,
            'monto_min' => 0, 'monto_max' => 5000, 'rol_aprobador' => 'gerente_area',
        ]);
        DB::table('niveles_aprobacion')->where('id', 3)->update([
            'monto_min' => 5000.01, 'orden' => 3,
        ]);
        DB::table('niveles_aprobacion')->where('id', 4)->update([
            'activo' => false,
        ]);
    }
};