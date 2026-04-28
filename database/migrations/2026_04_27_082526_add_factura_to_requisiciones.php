<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisiciones', function (Blueprint $table) {
            // Archivo de factura (obligatorio cuando es_pago_factura = true)
            $table->string('factura_path')->nullable()->after('es_pago_factura');
            $table->string('factura_nombre')->nullable()->after('factura_path');
        });
    }

    public function down(): void
    {
        Schema::table('requisiciones', function (Blueprint $table) {
            $table->dropColumn(['factura_path', 'factura_nombre']);
        });
    }
};