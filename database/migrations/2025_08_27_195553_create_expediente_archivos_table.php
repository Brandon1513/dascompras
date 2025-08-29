<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('expediente_archivos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('expediente_id')->constrained()->cascadeOnDelete();
            $t->enum('tipo', ['requi','factura','otros']);
            $t->string('nombre_original');
            $t->string('extension', 10)->nullable();
            $t->bigInteger('tamano')->nullable();
            $t->string('item_id')->nullable();
            $t->string('web_url')->nullable();
            $t->foreignId('subido_por')->constrained('users');
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('expediente_archivos');
    }
};

