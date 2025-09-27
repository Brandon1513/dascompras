<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aprobacion extends Model
{
    protected $table = 'aprobaciones';
    protected $fillable = [
        'requisicion_id','nivel_aprobacion_id','aprobador_id',
        'estado','comentarios','firmado_en','ip','firma_path'
    ];

    protected $casts = ['firmado_en' => 'datetime'];

    public function requisicion(): BelongsTo {
        return $this->belongsTo(Requisicion::class);
    }
    public function nivel(): BelongsTo {
        return $this->belongsTo(NivelAprobacion::class, 'nivel_aprobacion_id');
    }
    public function aprobador(): BelongsTo {
        return $this->belongsTo(User::class, 'aprobador_id');
    }
}
