<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NivelAprobacion extends Model
{
    protected $table = 'niveles_aprobacion';
    protected $fillable = ['nombre','monto_min','monto_max','rol_aprobador','orden'];

    public function aprobaciones(): HasMany {
        return $this->hasMany(Aprobacion::class);
    }
}
