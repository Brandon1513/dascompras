<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Primero actualizar los registros existentes con el valor incorrecto
        DB::statement("UPDATE aprobaciones SET estado = 'rechazada' WHERE estado = 'rechazado'");

        // Luego cambiar el ENUM para agregar 'rechazada' y mantener compatibilidad
        DB::statement("ALTER TABLE aprobaciones MODIFY COLUMN estado ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        DB::statement("UPDATE aprobaciones SET estado = 'rechazado' WHERE estado = 'rechazada'");
        DB::statement("ALTER TABLE aprobaciones MODIFY COLUMN estado ENUM('pendiente','aprobada','rechazado') NOT NULL DEFAULT 'pendiente'");
    }
};