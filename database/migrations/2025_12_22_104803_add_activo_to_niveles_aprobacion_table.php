<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // database/migrations/xxxx_add_activo_to_niveles_aprobacion_table.php
public function up(): void
{
    Schema::table('niveles_aprobacion', function (Blueprint $table) {
        $table->boolean('activo')->default(true)->after('orden');
    });
}
public function down(): void
{
    Schema::table('niveles_aprobacion', function (Blueprint $table) {
        $table->dropColumn('activo');
    });
}

};
