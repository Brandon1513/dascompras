<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('requisiciones', function (Blueprint $t) {
            // Nuevos FKs (usamos la MISMA tabla 'departamentos' para ambos)
            $t->foreignId('departamento_id')->nullable()->after('solicitante_id')->constrained('departamentos');
            $t->foreignId('centro_costo_id')->nullable()->after('departamento_id')->constrained('departamentos');
            // Nota: dejamos 'departamento' y 'centro_costo' (texto) por compatibilidad.
        });
    }
    public function down(): void {
        Schema::table('requisiciones', function (Blueprint $t) {
            $t->dropConstrainedForeignId('departamento_id');
            $t->dropConstrainedForeignId('centro_costo_id');
        });
    }
};
