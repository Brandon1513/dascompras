<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisicionItem extends Model
{
    protected $table = 'requisicion_items';
    protected $fillable = [
        'requisicion_id','descripcion','unidad','cantidad',
        'precio_unitario','subtotal','link_compra' // <- nuevo
    ];

    public function requisicion(): BelongsTo {
        return $this->belongsTo(Requisicion::class);
    }
}
