<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Requisicion extends Model
{
    protected $table = 'requisiciones';
          protected $fillable = [
        'folio','fecha_emision','solicitante_id',
        'departamento_id','centro_costo_id', // <- nuevos
        'departamento','centro_costo',       // (legacy texto, opcional)
        'justificacion','subtotal','iva','total','fecha_requerida','urgencia',
        'estado','recibido_por_id','fecha_recibido','area_recibe'
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_requerida' => 'date',
        'fecha_recibido' => 'datetime',
    ];
    public function departamentoRef(): BelongsTo {
        return $this->belongsTo(\App\Models\Departamento::class, 'departamento_id');
    }
    public function centroCostoRef(): BelongsTo {
        return $this->belongsTo(\App\Models\Departamento::class, 'centro_costo_id');
    }

    public function solicitante(): BelongsTo {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function recibidoPor(): BelongsTo {
        return $this->belongsTo(User::class, 'recibido_por_id');
    }

    public function items(): HasMany {
        return $this->hasMany(RequisicionItem::class);
    }

    public function aprobaciones(): HasMany {
        return $this->hasMany(Aprobacion::class);
    }
}
