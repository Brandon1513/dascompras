<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisicion_item_archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisicion_item_id')
                  ->constrained('requisicion_items')
                  ->cascadeOnDelete();

            // tipo: 'ficha_tecnica' | 'cotizacion' | 'otro'
            $table->enum('tipo', ['ficha_tecnica', 'cotizacion', 'otro'])->default('ficha_tecnica');

            $table->string('nombre_original');   // Nombre del archivo tal como lo subió el usuario
            $table->string('path');              // Ruta en storage (storage/public/requisiciones/archivos/)
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamanio')->nullable(); // Bytes

            $table->foreignId('subido_por_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisicion_item_archivos');
    }
};