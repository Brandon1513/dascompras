<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('requisiciones', function (Blueprint $t) {
            $t->id();
            $t->string('folio')->unique();
            $t->date('fecha_emision');
            $t->foreignId('solicitante_id')->constrained('users');
            $t->string('departamento')->nullable();
            $t->string('centro_costo')->nullable(); // MVP como texto (luego lo ligamos a catálogo)
            $t->text('justificacion');
            $t->decimal('subtotal', 12, 2)->default(0);
            $t->decimal('iva', 12, 2)->default(0);
            $t->decimal('total', 12, 2)->default(0);
            $t->date('fecha_requerida')->nullable();
            $t->enum('urgencia', ['normal','urgente'])->default('normal');
            $t->enum('estado', [
                'borrador','enviada','en_aprobacion',
                'rechazada','aprobada_final','cancelada','recibida'
            ])->default('borrador');

            // Recepción
            $t->foreignId('recibido_por_id')->nullable()->constrained('users');
            $t->dateTime('fecha_recibido')->nullable();
            $t->string('area_recibe')->nullable();

            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('requisiciones');
    }
};
