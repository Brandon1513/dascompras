<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisicion_items', function (Blueprint $table) {
            // Segundo impuesto opcional (ej. IEPS cuando ya hay IVA)
            $table->foreignId('tipo_impuesto_id_2')
                  ->nullable()
                  ->after('total_item')
                  ->constrained('tipos_impuesto')
                  ->nullOnDelete();

            $table->decimal('monto_impuesto_2', 12, 2)
                  ->default(0)
                  ->after('tipo_impuesto_id_2');
        });
    }

    public function down(): void
    {
        Schema::table('requisicion_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tipo_impuesto_id_2');
            $table->dropColumn('monto_impuesto_2');
        });
    }
};