<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones_internas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('requisicion_id')->nullable()->constrained('requisiciones')->nullOnDelete();
            $table->string('tipo', 50); // aprobada, rechazada, recibida, cerrada, accion_requerida, entregado
            $table->string('titulo', 255);
            $table->text('cuerpo')->nullable();
            $table->string('url')->nullable();
            $table->boolean('leida')->default(false);
            $table->timestamp('leida_en')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'leida', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones_internas');
    }
};