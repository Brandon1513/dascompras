<?php

// database/migrations/2025_10_xx_xxxx_add_manual_complete_to_expedientes.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('expedientes', function (Blueprint $t) {
            $t->boolean('completado_manual')->default(false)->index();
            $t->unsignedBigInteger('completado_por_id')->nullable();
            $t->timestamp('completado_en')->nullable();
            $t->text('completado_nota')->nullable();

            $t->foreign('completado_por_id')->references('id')->on('users')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('expedientes', function (Blueprint $t) {
            $t->dropConstrainedForeignId('completado_por_id');
            $t->dropColumn(['completado_manual','completado_en','completado_nota']);
        });
    }
};
