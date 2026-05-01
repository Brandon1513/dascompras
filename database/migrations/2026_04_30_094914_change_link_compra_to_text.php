<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisicion_items', function (Blueprint $table) {
            // Cambiar de VARCHAR a TEXT para soportar URLs largas
            $table->text('link_compra')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('requisicion_items', function (Blueprint $table) {
            $table->string('link_compra', 500)->nullable()->change();
        });
    }
};