<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisiciones', function (Blueprint $table) {
            // Motivo de rechazo de compras (visible al solicitante para que corrija)
            $table->text('motivo_rechazo_compras')
                  ->nullable()
                  ->after('observaciones_compras');

            // Quién de compras hizo la revisión y cuándo
            $table->foreignId('revisado_por_id')
                  ->nullable()
                  ->after('motivo_rechazo_compras')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->dateTime('revisado_en')
                  ->nullable()
                  ->after('revisado_por_id');
        });
    }

    public function down(): void
    {
        Schema::table('requisiciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('revisado_por_id');
            $table->dropColumn(['motivo_rechazo_compras', 'revisado_en']);
        });
    }
};