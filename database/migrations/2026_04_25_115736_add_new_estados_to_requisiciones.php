<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL requiere redefinir el enum completo para agregar valores
        DB::statement("
            ALTER TABLE requisiciones
            MODIFY COLUMN estado ENUM(
                'borrador',
                'enviada',
                'en_revision_compras',
                'rechazada_compras',
                'aprobada_compras',
                'en_aprobacion',
                'rechazada',
                'aprobada_final',
                'cancelada',
                'recibida'
            ) NOT NULL DEFAULT 'borrador'
        ");
    }

    public function down(): void
    {
        // Revertir al enum original (sin los nuevos estados)
        DB::statement("
            ALTER TABLE requisiciones
            MODIFY COLUMN estado ENUM(
                'borrador',
                'enviada',
                'en_aprobacion',
                'rechazada',
                'aprobada_final',
                'cancelada',
                'recibida'
            ) NOT NULL DEFAULT 'borrador'
        ");
    }
};