<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades_medida', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);           // Ej: "Pieza", "Kilogramo"
            $table->string('abreviatura', 20);        // Ej: "PZA", "KG"
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Insertar unidades base
        DB::table('unidades_medida')->insert([
            ['nombre' => 'Pieza',      'abreviatura' => 'PZA', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Kilogramo',  'abreviatura' => 'KG',  'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Litro',      'abreviatura' => 'LT',  'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Metro',      'abreviatura' => 'MT',  'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Caja',       'abreviatura' => 'CJA', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Paquete',    'abreviatura' => 'PQT', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Servicio',   'abreviatura' => 'SRV', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Hora',       'abreviatura' => 'HR',  'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Rollo',      'abreviatura' => 'RLL', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Par',        'abreviatura' => 'PAR', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades_medida');
    }
};