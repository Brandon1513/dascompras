<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('expedientes', function (Blueprint $t) {
            $t->id();
            $t->string('nombre_carpeta');
            $t->string('folder_item_id')->nullable();
            $t->string('folder_link')->nullable();
            $t->string('drive_id')->nullable();
            $t->string('folder_path')->nullable();
            $t->boolean('has_requi')->default(false);
            $t->boolean('has_factura')->default(false);
            $t->unsignedInteger('otros_count')->default(0);
            $t->enum('estado', ['incompleto','completo'])->default('incompleto');
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('expedientes');
    }
};

