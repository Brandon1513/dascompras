<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('requisicion_items', function (Blueprint $table) {
            // Si proveedor_sugerido YA existe, no lo agregues aquí.
            $table->string('ficha_tecnica_path')->nullable()->after('proveedor_sugerido');
            $table->string('ficha_tecnica_nombre')->nullable()->after('ficha_tecnica_path');
        });
    }

    public function down(): void
    {
        Schema::table('requisicion_items', function (Blueprint $table) {
            $table->dropColumn(['ficha_tecnica_path', 'ficha_tecnica_nombre']);
        });
    }
};
