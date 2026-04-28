<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnidadMedida extends Model
{
    protected $table = 'unidades_medida';

    protected $fillable = [
        'nombre',
        'abreviatura',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Scope para obtener solo las activas (para selects en formularios)
    public function scopeActivas($query)
    {
        return $query->where('activo', true)->orderBy('nombre');
    }

    public function requisicionItems(): HasMany
    {
        return $this->hasMany(RequisicionItem::class);
    }
}