<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Renombrar el rol en la tabla de Spatie
        DB::table('roles')
            ->where('name', 'gerente_area')
            ->update(['name' => 'gerente_operaciones']);

        // IMPORTANTE: Si tienes usuarios con este rol asignado,
        // sus permisos se mantienen porque la FK es por id, no por nombre.
        // No se necesita tocar model_has_roles.
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('name', 'gerente_operaciones')
            ->update(['name' => 'gerente_area']);
    }
};