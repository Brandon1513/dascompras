<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('niveles_aprobacion', function (Blueprint $t) {
            $t->id();
            $t->string('nombre');
            $t->decimal('monto_min', 12, 2)->default(0);
            $t->decimal('monto_max', 12, 2)->nullable(); // null = sin límite
            $t->string('rol_aprobador'); // nombre del rol Spatie
            $t->unsignedInteger('orden');
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('niveles_aprobacion');
    }
};
