<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisicionItemRetencion extends Model
{
    protected $table = 'requisicion_item_retenciones';

    protected $fillable = [
        'requisicion_item_id',
        'tipo_retencion_id',
        'monto',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(RequisicionItem::class, 'requisicion_item_id');
    }

    public function tipoRetencion(): BelongsTo
    {
        return $this->belongsTo(TipoRetencion::class, 'tipo_retencion_id');
    }
}