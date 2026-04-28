<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequisicionItem extends Model
{
    protected $table = 'requisicion_items';

    protected $fillable = [
        // Campos originales
        'requisicion_id',
        'descripcion',
        'unidad',               // Campo texto heredado (datos históricos)
        'cantidad',
        'precio_unitario',
        'subtotal',
        'link_compra',
        'proveedor_sugerido',
        'ficha_tecnica_path',   // Heredado (datos históricos)
        'ficha_tecnica_nombre', // Heredado (datos históricos)
        // ── Nuevos campos ──────────────────────────────
        'unidad_medida_id',     // FK al catálogo de unidades
        'tipo_impuesto_id',     // FK al catálogo de impuestos (nullable = sin impuesto)
        'monto_impuesto',       // Monto calculado del impuesto para esta partida
        'total_item',           // subtotal + monto_impuesto
    ];

    protected $casts = [
        'cantidad'        => 'decimal:3',
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2',
        'monto_impuesto'  => 'decimal:2',
        'total_item'      => 'decimal:2',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function requisicion(): BelongsTo
    {
        return $this->belongsTo(Requisicion::class);
    }

    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id');
    }

    public function tipoImpuesto(): BelongsTo
    {
        return $this->belongsTo(TipoImpuesto::class, 'tipo_impuesto_id');
    }

    // Todos los archivos adjuntos de esta partida (max 5)
    public function archivos(): HasMany
    {
        return $this->hasMany(RequisicionItemArchivo::class);
    }

    // Solo fichas técnicas
    public function fichasTecnicas(): HasMany
    {
        return $this->hasMany(RequisicionItemArchivo::class)->where('tipo', 'ficha_tecnica');
    }

    // Solo cotizaciones
    public function cotizaciones(): HasMany
    {
        return $this->hasMany(RequisicionItemArchivo::class)->where('tipo', 'cotizacion');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    /**
     * Etiqueta de unidad: usa el catálogo si existe, si no el texto libre heredado.
     * Ejemplo de uso en Blade: {{ $item->unidad_label }}
     */
    public function getUnidadLabelAttribute(): string
    {
        return $this->unidadMedida?->abreviatura ?? $this->unidad ?? '—';
    }

    /**
     * Indica si este item tiene archivos adjuntos nuevos (tabla requisicion_item_archivos)
     * o el archivo heredado (ficha_tecnica_path).
     */
    public function tieneArchivos(): bool
    {
        return $this->archivos()->exists() || !empty($this->ficha_tecnica_path);
    }

    // ─── Helpers de cálculo ───────────────────────────────────────────────────

    /**
     * Recalcula subtotal, monto_impuesto y total_item.
     * Llamar antes de guardar cuando cambian cantidad, precio o tipo_impuesto.
     *
     * Uso: $item->calcularTotales(); $item->save();
     */
    public function calcularTotales(): void
    {
        $this->subtotal = round((float) $this->cantidad * (float) $this->precio_unitario, 2);

        $porcentaje = (float) ($this->tipoImpuesto?->porcentaje ?? 0);
        $this->monto_impuesto = round($this->subtotal * ($porcentaje / 100), 2);
        $this->total_item     = round($this->subtotal + $this->monto_impuesto, 2);
    }
}