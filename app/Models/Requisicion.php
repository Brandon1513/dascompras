<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Aprobacion;

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
    public function aprobacionPendiente(): ?Aprobacion
    {
        // si ya vienen cargadas evita N+1
        if ($this->relationLoaded('aprobaciones')) {
            return $this->aprobaciones
                ->where('estado','pendiente')
                ->sortBy('created_at')
                ->first();
        }

        return $this->aprobaciones()
            ->where('estado','pendiente')
            ->orderBy('created_at')
            ->first();
    }

    /** Si la pendiente le corresponde a $user, la retorna; si no, null */
    public function aprobacionPendientePara(\App\Models\User $user): ?\App\Models\Aprobacion
    {
        $ap = $this->aprobacionPendiente();
        if (!$ap) return null;

        if ($ap->aprobador_id === $user->id) return $ap;

        if ($ap->nivel) {
            // Mapa por si en algún futuro un nivel acepta varios roles
            $map = [
                // 'direccion' => ['direccion'], // hoy es 1:1, pero lo dejamos listo
            ];
            $rol = $ap->nivel->rol_aprobador;
            $rolesQueSirven = $map[$rol] ?? [$rol];

            if ($user->hasAnyRole($rolesQueSirven)) return $ap;
        }

        return null;
    }
    public function scopeVisibleTo($query, User $user)
{
    // Admin y aprobadores ven todas
    if ($user->hasAnyRole(['administrador','compras','gerente_area','gerencia_adm','direccion'])) {
        return $query;
    }

    // Jefe: propias + de su equipo
    if ($user->hasRole('jefe')) {
        return $query->where(function($q) use ($user) {
            $q->where('solicitante_id', $user->id)
              ->orWhereHas('solicitante', fn($u) => $u->where('supervisor_id', $user->id));
        });
    }

    // Empleado (u otros roles “no aprobadores”): solo las propias
    return $query->where('solicitante_id', $user->id);
}

}
