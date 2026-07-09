<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionInterna extends Model
{
    protected $table = 'notificaciones_internas';

    protected $fillable = [
        'user_id',
        'requisicion_id',
        'tipo',
        'titulo',
        'cuerpo',
        'url',
        'leida',
        'leida_en',
    ];

    protected $casts = [
        'leida'    => 'boolean',
        'leida_en' => 'datetime',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requisicion(): BelongsTo
    {
        return $this->belongsTo(Requisicion::class);
    }

    // ── Helpers visuales ──────────────────────────────────────────────────
    public function getIconoAttribute(): string
    {
        return match($this->tipo) {
            'aprobada'        => '✅',
            'rechazada'       => '❌',
            'rechazada_compras' => '↩️',
            'recibida'        => '📦',
            'cerrada'         => '🔒',
            'accion_requerida'=> '⚡',
            'entregado'       => '📬',
            'revision'        => '🔍',
            default           => '🔔',
        };
    }

    public function getColorAttribute(): string
    {
        return match($this->tipo) {
            'aprobada'          => 'text-emerald-600 bg-emerald-50 border-emerald-200',
            'rechazada'         => 'text-rose-600 bg-rose-50 border-rose-200',
            'rechazada_compras' => 'text-orange-600 bg-orange-50 border-orange-200',
            'recibida'          => 'text-indigo-600 bg-indigo-50 border-indigo-200',
            'cerrada'           => 'text-cyan-600 bg-cyan-50 border-cyan-200',
            'accion_requerida'  => 'text-amber-600 bg-amber-50 border-amber-200',
            'entregado'         => 'text-green-600 bg-green-50 border-green-200',
            default             => 'text-purple-600 bg-purple-50 border-purple-200',
        };
    }

    // ── Método estático para crear notificaciones ─────────────────────────
    public static function enviar(
        int $userId,
        string $tipo,
        string $titulo,
        ?string $cuerpo = null,
        ?string $url = null,
        ?int $requisicionId = null
    ): self {
        return self::create([
            'user_id'        => $userId,
            'requisicion_id' => $requisicionId,
            'tipo'           => $tipo,
            'titulo'         => $titulo,
            'cuerpo'         => $cuerpo,
            'url'            => $url,
            'leida'          => false,
        ]);
    }

    // ── Enviar a múltiples usuarios ────────────────────────────────────────
    public static function enviarA(
        array $userIds,
        string $tipo,
        string $titulo,
        ?string $cuerpo = null,
        ?string $url = null,
        ?int $requisicionId = null
    ): void {
        foreach ($userIds as $userId) {
            self::enviar($userId, $tipo, $titulo, $cuerpo, $url, $requisicionId);
        }
    }
}