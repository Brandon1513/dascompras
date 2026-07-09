<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisicionActividad extends Model
{
    protected $table = 'requisicion_actividades';

    protected $fillable = [
        'requisicion_id',
        'user_id',
        'tipo',
        'estado_anterior',
        'estado_nuevo',
        'descripcion',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────
    public function requisicion(): BelongsTo
    {
        return $this->belongsTo(Requisicion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers visuales ──────────────────────────────────────────────────
    public function getIconoAttribute(): string
    {
        return match($this->tipo) {
            'creada'       => '✏️',
            'enviada'      => '📤',
            'revisada'     => '🔍',
            'aprobada'     => '✅',
            'rechazada'    => '❌',
            'rechazada_compras' => '↩️',
            'recibida'     => '📦',
            'cerrada'      => '🔒',
            'editada'      => '📝',
            'comentario'   => '💬',
            'oc_netsuite'  => '🔗',
            'entregado'    => '📬',
            default        => '•',
        };
    }

    public function getColorAttribute(): string
    {
        return match($this->tipo) {
            'creada'            => 'bg-gray-100 text-gray-600 border-gray-200',
            'enviada'           => 'bg-violet-100 text-violet-700 border-violet-200',
            'revisada'          => 'bg-blue-100 text-blue-700 border-blue-200',
            'aprobada'          => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'rechazada'         => 'bg-rose-100 text-rose-700 border-rose-200',
            'rechazada_compras' => 'bg-orange-100 text-orange-700 border-orange-200',
            'recibida'          => 'bg-indigo-100 text-indigo-700 border-indigo-200',
            'cerrada'           => 'bg-cyan-100 text-cyan-700 border-cyan-200',
            'editada'           => 'bg-amber-100 text-amber-700 border-amber-200',
            'comentario'        => 'bg-purple-100 text-purple-700 border-purple-200',
            'oc_netsuite'       => 'bg-teal-100 text-teal-700 border-teal-200',
            'entregado'         => 'bg-green-100 text-green-700 border-green-200',
            default             => 'bg-gray-100 text-gray-600 border-gray-200',
        };
    }

    public function getColorLineaAttribute(): string
    {
        return match($this->tipo) {
            'aprobada'   => '#10b981',
            'rechazada'  => '#ef4444',
            'rechazada_compras' => '#f97316',
            'cerrada'    => '#06b6d4',
            'recibida'   => '#6366f1',
            'enviada'    => '#7c3aed',
            default      => '#d1d5db',
        };
    }

    // ── Registro rápido (método estático) ────────────────────────────────
    public static function registrar(
        int $requisicionId,
        string $tipo,
        ?string $descripcion = null,
        ?int $userId = null,
        ?string $estadoAnterior = null,
        ?string $estadoNuevo = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'requisicion_id'  => $requisicionId,
            'user_id'         => $userId ?? auth()->id(),
            'tipo'            => $tipo,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => $estadoNuevo,
            'descripcion'     => $descripcion,
            'metadata'        => $metadata,
        ]);
    }
}