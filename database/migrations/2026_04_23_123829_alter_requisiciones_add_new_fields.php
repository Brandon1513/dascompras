<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisiciones', function (Blueprint $table) {
            // --- Pago de factura ---
            // Cuando es true, indica que es un pago de factura (no una compra nueva).
            // Sigue el mismo flujo de aprobaciones, solo cambia la presentación.
            $table->boolean('es_pago_factura')->default(false)->after('total');

            // --- Método de pago (lo asigna el área de compras) ---
            $table->enum('metodo_pago', ['tarjeta', 'transferencia'])
                  ->nullable()
                  ->after('es_pago_factura');

            // --- Observaciones del área de compras ---
            $table->text('observaciones_compras')->nullable()->after('metodo_pago');
        });
    }

    public function down(): void
    {
        Schema::table('requisiciones', function (Blueprint $table) {
            $table->dropColumn(['es_pago_factura', 'metodo_pago', 'observaciones_compras']);
        });
    }
};