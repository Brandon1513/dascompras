<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('requisicion_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('requisicion_id')->constrained('requisiciones')->cascadeOnDelete();
            $t->string('descripcion');
            $t->string('unidad', 20)->nullable();
            $t->decimal('cantidad', 12, 3);
            $t->decimal('precio_unitario', 12, 2)->default(0);
            $t->decimal('subtotal', 12, 2)->default(0); // cantidad * precio
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('requisicion_items');
    }
};
