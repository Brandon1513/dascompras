<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TipoRetencion extends Model
{
    protected $table = 'tipos_retencion';

    protected $fillable = ['nombre', 'clave', 'porcentaje', 'activo'];

    protected $casts = [
        'porcentaje' => 'decimal:4',
        'activo'     => 'boolean',
    ];

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}