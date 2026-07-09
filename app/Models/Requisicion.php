<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Requisicion extends Model
{
    protected $table = 'requisiciones';

    protected $fillable = [
        'folio',
        'fecha_emision',
        'solicitante_id',
        'departamento_id',
        'centro_costo_id',
        'departamento',
        'centro_costo',
        'justificacion',
        'subtotal',
        'iva',
        'total',
        'fecha_requerida',
        'urgencia',
        'estado',
        // Recepción
        'recibido_por_id',
        'fecha_recibido',
        'area_recibe',
        'recibe_nombre',
        'firma_recepcion_path',
        // Campos de compras
        'es_pago_factura',
        'factura_path',
        'factura_nombre',
        'tiene_factura',
        'uuid_factura',
        'factura_compras_path',
        'factura_compras_nombre',
        'cerrado_por_id',
        'cerrado_en',
        'notas_cierre',

        'oc_netsuite',
        'metodo_pago',
        'observaciones_compras',
        // Campos de revisión (nuevo flujo)
        'motivo_rechazo_compras',
        'revisado_por_id',
        'revisado_en',

        //factura

    ];

    protected $casts = [
        'fecha_emision'   => 'date',
        'fecha_requerida' => 'date',
        'fecha_recibido'  => 'datetime',
        'revisado_en'     => 'datetime',
        'es_pago_factura' => 'boolean',
        'subtotal'        => 'decimal:2',
        'iva'             => 'decimal:2',
        'total'           => 'decimal:2',
        'tiene_factura' => 'boolean',
         'cerrado_en'    => 'datetime',

    ];

    // ─── Constantes de estado ─────────────────────────────────────────────

    const ESTADOS_LABEL = [
        'borrador'              => 'Borrador',
        'enviada'               => 'Enviada',
        'en_revision_compras'   => 'En revisión de compras',
        'rechazada_compras'     => 'Rechazada por compras',
        'aprobada_compras'      => 'Aprobada por compras',
        'en_aprobacion'         => 'En aprobación',
        'rechazada'             => 'Rechazada',
        'aprobada_final'        => 'Aprobada',
        'cancelada'             => 'Cancelada',
        'recibida'              => 'Recibida',
        'pendiente_cierre'      => 'Pendiente de cierre',
    ];

    const ESTADOS_COLOR = [
        'borrador'              => ['bg' => 'bg-gray-100',    'text' => 'text-gray-600',    'border' => 'border-gray-200'],
        'enviada'               => ['bg' => 'bg-sky-50',      'text' => 'text-sky-700',      'border' => 'border-sky-200'],
        'en_revision_compras'   => ['bg' => 'bg-violet-50',   'text' => 'text-violet-700',   'border' => 'border-violet-200'],
        'rechazada_compras'     => ['bg' => 'bg-orange-50',   'text' => 'text-orange-700',   'border' => 'border-orange-200'],
        'aprobada_compras'      => ['bg' => 'bg-cyan-50',     'text' => 'text-cyan-700',     'border' => 'border-cyan-200'],
        'en_aprobacion'         => ['bg' => 'bg-amber-50',    'text' => 'text-amber-700',    'border' => 'border-amber-200'],
        'rechazada'             => ['bg' => 'bg-rose-50',     'text' => 'text-rose-700',     'border' => 'border-rose-200'],
        'aprobada_final'        => ['bg' => 'bg-emerald-50',  'text' => 'text-emerald-700',  'border' => 'border-emerald-200'],
        'cancelada'             => ['bg' => 'bg-gray-100',    'text' => 'text-gray-400',     'border' => 'border-gray-200'],
        'recibida'              => ['bg' => 'bg-teal-50',     'text' => 'text-teal-700',     'border' => 'border-teal-200'],
        'pendiente_cierre'      => ['bg' => 'bg-cyan-50',     'text' => 'text-cyan-700',     'border' => 'border-cyan-200'],
    ];

    const METODOS_PAGO_LABEL = [
        'tarjeta'       => 'Tarjeta',
        'transferencia' => 'Transferencia',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────

      public function actividades(): HasMany
    {
        return $this->hasMany(RequisicionActividad::class)->orderBy('created_at');
    }
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function centroCosto(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'centro_costo_id');
    }

    // Aliases de compatibilidad (usados en vistas y lógica de aprobación)
    public function departamentoRef(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function centroCostoRef(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'centro_costo_id');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recibido_por_id');
    }

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por_id');
    }
    public function cerradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrado_por_id');
    }


    public function items(): HasMany
    {
        return $this->hasMany(RequisicionItem::class);
    }

    public function aprobaciones(): HasMany
    {
        return $this->hasMany(Aprobacion::class);
    }

    // ─── Lógica de aprobaciones ───────────────────────────────────────────

    public function aprobacionPendiente(): ?\App\Models\Aprobacion
    {
        if ($this->relationLoaded('aprobaciones')) {
            return $this->aprobaciones
                ->where('estado', 'pendiente')
                ->sortBy(fn($a) => $a->nivel?->orden ?? 999)
                ->first();
        }

        return $this->aprobaciones()
            ->where('estado', 'pendiente')
            ->orderBy('created_at')
            ->first();
    }

    public function aprobacionPendientePara(\App\Models\User $user): ?\App\Models\Aprobacion
    {
        $ap = $this->aprobacionPendiente();
        if (!$ap) return null;

        if (!is_null($ap->aprobador_id)) {
            return ((int) $ap->aprobador_id === (int) $user->id) ? $ap : null;
        }

        $rol = $ap->nivel?->rol_aprobador;
        if (!$rol) return null;

        // Gerente de operaciones firma según asignación del departamento
        if ($rol === 'gerente_operaciones') {
            $gerenteId = $this->departamentoRef()->value('gerente_id');
            if ((int) $gerenteId !== (int) $user->id) return null;
            return $user->hasRole('gerente_operaciones') ? $ap : null;
        }

        return $user->hasRole($rol) ? $ap : null;
    }

    // ─── Scopes ───────────────────────────────────────────────────────────

    public function scopeVisibleTo($query, \App\Models\User $user)
    {
        // Compras y admin ven todo
        if ($user->hasAnyRole(['administrador', 'compras', 'gerencia_adm'])) {
            return $query;
        }

        // Gerente de operaciones: sus requisiciones + las de su área
        if ($user->hasRole('gerente_operaciones')) {
            return $query->where(function ($q) use ($user) {
                $q->where('solicitante_id', $user->id)
                  ->orWhereHas('solicitante', fn($u) => $u->where('supervisor_id', $user->id))
                  ->orWhereHas('departamentoRef', fn($d) => $d->where('gerente_id', $user->id));
            });
        }

        // Jefe: sus requisiciones + las de sus subordinados + las que tiene pendiente aprobar
        if ($user->roles->pluck('name')->map(fn($r) => strtolower($r))->contains('jefe')) {
            return $query->where(function ($q) use ($user) {
                $q->where('solicitante_id', $user->id)
                ->orWhereHas('solicitante', fn($u) => $u->where('supervisor_id', $user->id))
                ->orWhereHas('aprobaciones', fn($a) => $a->where('aprobador_id', $user->id));
            });
        }

        // Empleado: solo las propias
        return $query->where('solicitante_id', $user->id);
    }

    public function scopePorMetodoPago($query, string $metodo)
    {
        return $query->where('metodo_pago', $metodo);
    }

    public function scopePagoFactura($query, bool $solo = true)
    {
        return $query->where('es_pago_factura', $solo);
    }

    // ─── Accessors ────────────────────────────────────────────────────────

    public function getEstadoLabelAttribute(): string
    {
        return self::ESTADOS_LABEL[$this->estado] ?? $this->estado;
    }

    public function getEstadoColorAttribute(): array
    {
        return self::ESTADOS_COLOR[$this->estado] ?? self::ESTADOS_COLOR['borrador'];
    }

    public function getEstadoBgAttribute(): string
    {
        return $this->estadoColor['bg'] ?? 'bg-gray-100';
    }

    public function getEstadoTextAttribute(): string
    {
        return $this->estadoColor['text'] ?? 'text-gray-600';
    }

    public function getEstadoBorderAttribute(): string
    {
        return $this->estadoColor['border'] ?? 'border-gray-200';
    }

    public function getMetodoPagoLabelAttribute(): string
    {
        return self::METODOS_PAGO_LABEL[$this->metodo_pago] ?? '—';
    }

        /**
     * Total neto a pagar (total - retenciones).
     * Si la requisición no es pago de factura, es igual al total.
     * Requiere que los items estén cargados con eager loading.
     */
    public function getTotalNetoAttribute(): float
    {
        if (!$this->es_pago_factura) {
            return (float) $this->total;
        }
 
        // Si los items están cargados y tienen total_neto, usarlos
        if ($this->relationLoaded('items')) {
            $suma = $this->items->sum(fn($it) => (float) ($it->total_neto ?? $it->total_item ?? 0));
            if ($suma > 0) return round($suma, 2);
        }
 
        return (float) $this->total;
    }
 
    /**
     * Total de retenciones de toda la requisición.
     */
    public function getTotalRetencionesAttribute(): float
    {
        if (!$this->es_pago_factura) return 0;
 
        if ($this->relationLoaded('items')) {
            return round($this->items->sum(fn($it) => (float) ($it->monto_retenciones ?? 0)), 2);
        }
 
        return 0;
    }
 


    // ─── Helpers de estado ────────────────────────────────────────────────

    public function esBorrador(): bool              { return $this->estado === 'borrador'; }
    public function estaEnRevisionCompras(): bool   { return $this->estado === 'en_revision_compras'; }
    public function estaRechazadaPorCompras(): bool { return $this->estado === 'rechazada_compras'; }
    public function estaAprobadaPorCompras(): bool  { return $this->estado === 'aprobada_compras'; }
    public function estaEnAprobacion(): bool        { return $this->estado === 'en_aprobacion'; }
    public function estaAprobada(): bool            { return $this->estado === 'aprobada_final'; }
    public function estaRecibida(): bool            { return $this->estado === 'recibida'; }
    public function estaRechazada(): bool           { return $this->estado === 'rechazada'; }

    public function estaPendienteCierre(): bool { return $this->estado === 'pendiente_cierre'; }

    // El usuario puede editar solo en borrador o rechazada por compras
    public function puedeEditarSolicitante(\App\Models\User $user): bool
    {
        if ($user->id !== $this->solicitante_id) return false;
        return in_array($this->estado, ['borrador', 'rechazada_compras']);
    }

    // Compras puede editar hasta que llegue a aprobación final
    public function puedeEditarCompras(): bool
    {
        return !in_array($this->estado, ['aprobada_final', 'recibida', 'cancelada']);
    }
}