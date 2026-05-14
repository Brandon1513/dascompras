<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequisicionItem extends Model
{
    protected $table = 'requisicion_items';

    protected $fillable = [
        'requisicion_id',
        'descripcion',
        'unidad',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'link_compra',
        'proveedor_sugerido',
        'ficha_tecnica_path',
        'ficha_tecnica_nombre',
        'unidad_medida_id',
        'tipo_impuesto_id',
        'monto_impuesto',
        'total_item',
        // Segundo impuesto
        'tipo_impuesto_id_2',
        'monto_impuesto_2',
        'metodo_pago',
        'monto_retenciones',
        'total_neto',

    ];

    protected $casts = [
        'cantidad'         => 'integer',
        'precio_unitario'  => 'decimal:2',
        'subtotal'         => 'decimal:2',
        'monto_impuesto'   => 'decimal:2',
        'monto_impuesto_2' => 'decimal:2',
        'total_item'       => 'decimal:2',
        'monto_retenciones' => 'decimal:2',
        'total_neto'        => 'decimal:2',
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

    public function tipoImpuesto2(): BelongsTo
    {
        return $this->belongsTo(TipoImpuesto::class, 'tipo_impuesto_id_2');
    }
        public function retenciones(): HasMany
    {
        return $this->hasMany(RequisicionItemRetencion::class);
    }
 
    // Tipos de retención con monto calculado
    public function tiposRetencion()
    {
        return $this->belongsToMany(
            TipoRetencion::class,
            'requisicion_item_retenciones',
            'requisicion_item_id',
            'tipo_retencion_id'
        )->withPivot('monto')->withTimestamps();
    }


    public function archivos(): HasMany
    {
        return $this->hasMany(RequisicionItemArchivo::class);
    }

    public function fichasTecnicas(): HasMany
    {
        return $this->hasMany(RequisicionItemArchivo::class)->where('tipo', 'ficha_tecnica');
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(RequisicionItemArchivo::class)->where('tipo', 'cotizacion');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getUnidadLabelAttribute(): string
    {
        return $this->unidadMedida?->abreviatura ?? $this->unidad ?? '—';
    }

    public function tieneArchivos(): bool
    {
        return $this->archivos()->exists() || !empty($this->ficha_tecnica_path);
    }

    // ─── Helpers de cálculo ───────────────────────────────────────────────────

    /**
     * Recalcula subtotal, ambos impuestos y total_item.
     * Ambos impuestos calculan sobre el subtotal (Opción A).
     */
    public function calcularTotales(): void
    {
        $this->subtotal = round((float) $this->cantidad * (float) $this->precio_unitario, 2);
 
        // Impuesto 1
        $pct1 = (float) ($this->tipoImpuesto?->porcentaje ?? 0);
        $this->monto_impuesto = round($this->subtotal * ($pct1 / 100), 2);
 
        // Impuesto 2
        $pct2 = (float) ($this->tipoImpuesto2?->porcentaje ?? 0);
        $this->monto_impuesto_2 = round($this->subtotal * ($pct2 / 100), 2);
 
        $this->total_item = round(
            $this->subtotal + $this->monto_impuesto + $this->monto_impuesto_2,
            2
        );
 
        // Retenciones (solo si ya están cargadas)
        $montoRet = 0;
        if ($this->relationLoaded('retenciones')) {
            foreach ($this->retenciones as $ret) {
                $pctRet    = (float) ($ret->tipoRetencion?->porcentaje ?? 0);
                $montoRet += round($this->subtotal * ($pctRet / 100), 2);
            }
        }
 
        $this->monto_retenciones = round($montoRet, 2);
        $this->total_neto        = round($this->total_item - $this->monto_retenciones, 2);
    }
 

}