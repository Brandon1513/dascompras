<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisicion_items', function (Blueprint $table) {
            // Método de pago por partida
            $table->string('metodo_pago', 20)->nullable()->after('total_item')
                  ->comment('transferencia | tarjeta | efectivo');

            // Totales de retenciones calculados (para histórico)
            $table->decimal('monto_retenciones', 12, 2)->default(0)->after('metodo_pago');

            // Total neto = total_item - monto_retenciones
            $table->decimal('total_neto', 12, 2)->default(0)->after('monto_retenciones');
        });
    }

    public function down(): void
    {
        Schema::table('requisicion_items', function (Blueprint $table) {
            $table->dropColumn(['metodo_pago', 'monto_retenciones', 'total_neto']);
        });
    }
};