<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('requisicion_items', function (Blueprint $table) {
        $table->string('proveedor_sugerido')->nullable()->after('link_compra');
    });
}

public function down(): void
{
    Schema::table('requisicion_items', function (Blueprint $table) {
        $table->dropColumn('proveedor_sugerido');
    });
}

};
