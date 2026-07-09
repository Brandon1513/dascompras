<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisicion_items', function (Blueprint $table) {
            $table->boolean('entregado')->default(false)->after('proveedor_sugerido');
            $table->timestamp('entregado_en')->nullable()->after('entregado');
            $table->foreignId('entregado_por_id')->nullable()->constrained('users')->nullOnDelete()->after('entregado_en');
        });
    }

    public function down(): void
    {
        Schema::table('requisicion_items', function (Blueprint $table) {
            $table->dropForeign(['entregado_por_id']);
            $table->dropColumn(['entregado', 'entregado_en', 'entregado_por_id']);
        });
    }
};