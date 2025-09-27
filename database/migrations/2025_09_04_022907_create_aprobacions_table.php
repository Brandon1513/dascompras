<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('aprobaciones', function (Blueprint $t) {
            $t->id();
            $t->foreignId('requisicion_id')->constrained('requisiciones')->cascadeOnDelete();
            $t->foreignId('nivel_aprobacion_id')->constrained('niveles_aprobacion');
            $t->foreignId('aprobador_id')->nullable()->constrained('users');
            $t->enum('estado', ['pendiente','aprobado','rechazado'])->default('pendiente');
            $t->text('comentarios')->nullable();
            $t->dateTime('firmado_en')->nullable();
            $t->string('ip', 45)->nullable();
            $t->string('firma_path')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('aprobaciones');
    }
};
