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
        'departamento_id','centro_costo_id',
        'departamento','centro_costo',
        'justificacion','subtotal','iva','total','fecha_requerida','urgencia',
        'estado','recibido_por_id','fecha_recibido','area_recibe', 'recibe_nombre', 'firma_recepcion_path',
        // 'aplica_iva' // <- solo si existe en tu tabla
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_requerida' => 'date',
        'fecha_recibido' => 'datetime',
        // 'aplica_iva' => 'boolean', // <- solo si existe en tu tabla
    ];

    // ✅ Nombres estándar (para tu Blade)
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Departamento::class, 'departamento_id');
    }

    public function centroCosto(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Departamento::class, 'centro_costo_id');
    }

    // 🔁 Alias (compatibilidad)
    public function departamentoRef(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Departamento::class, 'departamento_id');
    }

    public function centroCostoRef(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Departamento::class, 'centro_costo_id');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'solicitante_id');
    }

    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'recibido_por_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(\App\Models\RequisicionItem::class);
    }

    public function aprobaciones(): HasMany
    {
        return $this->hasMany(\App\Models\Aprobacion::class);
    }

    public function aprobacionPendiente(): ?\App\Models\Aprobacion
{
    if ($this->relationLoaded('aprobaciones')) {
        return $this->aprobaciones
            ->where('estado','pendiente')
            ->sortBy(fn($a) => $a->nivel?->orden ?? 999)
            ->first();
    }

    return $this->aprobaciones()
        ->where('estado','pendiente')
        ->orderBy('created_at')
        ->first();
}


    public function aprobacionPendientePara(\App\Models\User $user): ?\App\Models\Aprobacion
{
    $ap = $this->aprobacionPendiente();
    if (!$ap) return null;

    // 1) Si la aprobación es para una persona específica (aprobador_id), solo esa persona puede firmar
    if (!is_null($ap->aprobador_id)) {
        return ((int)$ap->aprobador_id === (int)$user->id) ? $ap : null;
    }

    // 2) Si NO hay aprobador_id, se firma por rol (ej. gerencia_adm)
    $rol = $ap->nivel?->rol_aprobador;
    if (!$rol) return null;

    // ✅ Caso especial: gerente_area (si algún día lo dejas null por rol)
    // Solo puede firmar si el usuario es el gerente asignado del departamento de la requisición
    if ($rol === 'gerente_area') {
        $gerenteId = $this->departamentoRef()->value('gerente_id'); // o $this->departamento()->value('gerente_id')
        if ((int)$gerenteId !== (int)$user->id) return null;

        return $user->hasRole('gerente_area') ? $ap : null;
    }

    // ✅ Caso típico: gerencia_adm firma por rol
    return $user->hasRole($rol) ? $ap : null;
}


    // ✅ ESTE ES EL QUE TE ESTÁ FALTANDO (o se perdió)
    public function scopeVisibleTo($query, \App\Models\User $user)
{
    // Roles con visibilidad total (ajusta a tus necesidades)
    if ($user->hasAnyRole(['administrador','compras','gerencia_adm'])) {
        return $query;
    }

    // Gerente de área: ve lo suyo + lo de su área
    if ($user->hasRole('gerente_area')) {
        return $query->where(function ($q) use ($user) {
            $q->where('solicitante_id', $user->id)
              ->orWhereHas('solicitante', fn($u) => $u->where('supervisor_id', $user->id))
              ->orWhereHas('departamentoRef', fn($d) => $d->where('gerente_id', $user->id));
        });
    }

    // Jefe directo: ve lo suyo y sus subordinados
    if ($user->hasRole('jefe')) {
        return $query->where(function($q) use ($user) {
            $q->where('solicitante_id', $user->id)
              ->orWhereHas('solicitante', fn($u) => $u->where('supervisor_id', $user->id));
        });
    }

    // Usuario normal: solo lo suyo
    return $query->where('solicitante_id', $user->id);
}

}
