<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisicion_items', function (Blueprint $table) {
            // FK a unidades_medida (reemplaza el campo 'unidad' texto libre)
            // Dejamos el campo 'unidad' existente por compatibilidad con datos históricos.
            $table->foreignId('unidad_medida_id')
                  ->nullable()
                  ->after('unidad')
                  ->constrained('unidades_medida')
                  ->nullOnDelete();

            // FK a tipos_impuesto (nullable = sin impuesto / no aplica)
            $table->foreignId('tipo_impuesto_id')
                  ->nullable()
                  ->after('subtotal')
                  ->constrained('tipos_impuesto')
                  ->nullOnDelete();

            // Monto calculado del impuesto para esa partida (guardado para histórico)
            $table->decimal('monto_impuesto', 12, 2)->default(0)->after('tipo_impuesto_id');

            // Total de la partida incluyendo impuesto
            $table->decimal('total_item', 12, 2)->default(0)->after('monto_impuesto');
        });
    }

    public function down(): void
    {
        Schema::table('requisicion_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unidad_medida_id');
            $table->dropConstrainedForeignId('tipo_impuesto_id');
            $table->dropColumn(['monto_impuesto', 'total_item']);
        });
    }
};