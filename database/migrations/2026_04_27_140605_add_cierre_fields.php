<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1) Nuevo estado en el enum ────────────────────────────────────
        DB::statement("
            ALTER TABLE requisiciones
            MODIFY COLUMN estado ENUM(
                'borrador',
                'enviada',
                'en_revision_compras',
                'rechazada_compras',
                'aprobada_compras',
                'en_aprobacion',
                'rechazada',
                'aprobada_final',
                'pendiente_cierre',
                'cancelada',
                'recibida'
            ) NOT NULL DEFAULT 'borrador'
        ");

        // ── 2) Nuevos campos ──────────────────────────────────────────────
        Schema::table('requisiciones', function (Blueprint $table) {
            // ¿Se tiene factura? (null = aún no definido, true = sí, false = no)
            $table->boolean('tiene_factura')
                  ->nullable()
                  ->after('factura_nombre');

            // UUID fiscal de la factura (CFDI)
            $table->string('uuid_factura', 36)
                  ->nullable()
                  ->after('tiene_factura');

            // Factura que sube compras al momento del cierre
            $table->string('factura_compras_path')->nullable()->after('uuid_factura');
            $table->string('factura_compras_nombre')->nullable()->after('factura_compras_path');

            // Quién cerró y cuándo
            $table->foreignId('cerrado_por_id')
                  ->nullable()
                  ->after('factura_compras_nombre')
                  ->constrained('users')
                  ->nullOnDelete();

            $table->dateTime('cerrado_en')->nullable()->after('cerrado_por_id');

            // Notas de cierre (opcional)
            $table->text('notas_cierre')->nullable()->after('cerrado_en');
        });
    }

    public function down(): void
    {
        Schema::table('requisiciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cerrado_por_id');
            $table->dropColumn([
                'tiene_factura',
                'uuid_factura',
                'factura_compras_path',
                'factura_compras_nombre',
                'cerrado_en',
                'notas_cierre',
            ]);
        });

        DB::statement("
            ALTER TABLE requisiciones
            MODIFY COLUMN estado ENUM(
                'borrador','enviada','en_revision_compras','rechazada_compras',
                'aprobada_compras','en_aprobacion','rechazada','aprobada_final',
                'cancelada','recibida'
            ) NOT NULL DEFAULT 'borrador'
        ");
    }
};