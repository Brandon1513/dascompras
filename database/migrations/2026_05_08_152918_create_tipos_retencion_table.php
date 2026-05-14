<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_retencion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');           // "ISR 1.25%"
            $table->string('clave', 30)->unique(); // "ISR_125"
            $table->decimal('porcentaje', 8, 4); // 1.25, 10.667, 10.0
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_retencion');
    }
};