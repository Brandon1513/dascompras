<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoImpuesto extends Model
{
    protected $table = 'tipos_impuesto';

    protected $fillable = [
        'nombre',
        'clave',
        'porcentaje',
        'activo',
    ];

    protected $casts = [
        'porcentaje' => 'decimal:2',
        'activo'     => 'boolean',
    ];

    // Scope para obtener solo los activos (para selects en formularios)
    public function scopeActivos($query)
    {
        return $query->where('activo', true)->orderBy('nombre');
    }

    // Verifica si este impuesto implica un cálculo real (porcentaje > 0)
    public function aplicaMonto(): bool
    {
        return $this->porcentaje > 0;
    }

    public function requisicionItems(): HasMany
    {
        return $this->hasMany(RequisicionItem::class);
    }
}