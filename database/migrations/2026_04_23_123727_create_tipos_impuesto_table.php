<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_impuesto', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);          // Ej: "IVA 16%", "IEPS", "IVA 0%", "Exento"
            $table->string('clave', 30)->unique();   // Ej: "IVA_16", "IEPS", "IVA_0", "EXENTO"
            $table->decimal('porcentaje', 5, 2);     // Ej: 16.00, 8.00, 0.00
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Insertar impuestos base
        DB::table('tipos_impuesto')->insert([
            ['nombre' => 'IVA 16%',  'clave' => 'IVA_16',  'porcentaje' => 16.00, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'IVA 0%',   'clave' => 'IVA_0',   'porcentaje' => 0.00,  'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'IEPS 8%',  'clave' => 'IEPS_8',  'porcentaje' => 8.00,  'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Exento',   'clave' => 'EXENTO',  'porcentaje' => 0.00,  'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_impuesto');
    }
};