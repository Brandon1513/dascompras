<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisicion_actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisicion_id')->constrained('requisiciones')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 50); // creada, enviada, revisada, aprobada, rechazada, recibida, cerrada, editada, comentario, oc_netsuite, entregado
            $table->string('estado_anterior', 50)->nullable();
            $table->string('estado_nuevo', 50)->nullable();
            $table->text('descripcion')->nullable();
            $table->json('metadata')->nullable(); // datos extra como monto, folio, etc.
            $table->timestamps();

            $table->index(['requisicion_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisicion_actividades');
    }
};