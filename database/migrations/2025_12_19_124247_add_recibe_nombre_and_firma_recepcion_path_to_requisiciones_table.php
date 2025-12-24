<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('requisiciones', function (Blueprint $table) {
            $table->string('recibe_nombre', 255)->nullable()->after('area_recibe');
            $table->string('firma_recepcion_path', 255)->nullable()->after('recibe_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('requisiciones', function (Blueprint $table) {
            $table->dropColumn(['recibe_nombre', 'firma_recepcion_path']);
        });
    }
};
