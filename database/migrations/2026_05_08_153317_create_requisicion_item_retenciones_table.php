<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisicion_item_retenciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('requisicion_item_id')
                  ->constrained('requisicion_items')
                  ->cascadeOnDelete();

            $table->foreignId('tipo_retencion_id')
                  ->constrained('tipos_retencion')
                  ->restrictOnDelete();

            $table->decimal('monto', 12, 2)->default(0);

            $table->timestamps();

            // Nombre corto para evitar el límite de 64 chars de MySQL
            $table->unique(
                ['requisicion_item_id', 'tipo_retencion_id'],
                'ret_item_tipo_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisicion_item_retenciones');
    }
};