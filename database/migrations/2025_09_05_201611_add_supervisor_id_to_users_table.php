<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            // Agrega la columna solo si no existe
            if (!Schema::hasColumn('users', 'supervisor_id')) {
                $t->foreignId('supervisor_id')->nullable()->after('id')
                  ->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            } else {
                // Si la columna existe pero sin FK, intenta agregar la FK
                // (si ya existiera, MySQL la ignora o lanzará error benigno en entornos que ya la tengan)
                try {
                    $t->foreign('supervisor_id')->references('id')->on('users')
                      ->nullOnDelete()->cascadeOnUpdate();
                } catch (\Throwable $e) {
                    // opcional: loguea si quieres, pero no detengas la migración
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            // Quita FK si existe y luego la columna
            try { $t->dropForeign(['supervisor_id']); } catch (\Throwable $e) {}
            if (Schema::hasColumn('users', 'supervisor_id')) {
                $t->dropColumn('supervisor_id');
            }
        });
    }
};
