<?php

namespace App\Policies;

use App\Models\Requisicion;
use App\Models\User;

class RequisicionPolicy
{
    /**
     * Ver una requisición.
     */
    public function view(User $user, Requisicion $r): bool
    {
        // Dueño
        if ($r->solicitante_id === $user->id) return true;

        // Jefe directo del solicitante
        if ($r->solicitante && $r->solicitante->supervisor_id === $user->id) return true;

        // Roles con visibilidad total (gerente_area renombrado a gerente_operaciones)
        if ($user->hasAnyRole(['administrador', 'compras', 'gerente_operaciones', 'gerencia_adm'])) return true;

        // Si ya participó en esta requisición (aprobó/rechazó antes)
        if ($r->relationLoaded('aprobaciones')) {
            if ($r->aprobaciones->where('aprobador_id', $user->id)->isNotEmpty()) return true;
        } else {
            if ($r->aprobaciones()->where('aprobador_id', $user->id)->exists()) return true;
        }

        // Aprobadores a los que les toca AHORA (pendiente)
        return (bool) $r->aprobacionPendientePara($user);
    }

    /**
     * Editar una requisición.
     * - Solicitante: solo en borrador o rechazada_compras
     * - Compras/Admin: mientras no esté aprobada_final o recibida
     */
    public function update(User $user, Requisicion $r): bool
    {
        // Compras y admin pueden editar en casi cualquier estado
        if ($user->hasAnyRole(['compras', 'administrador'])) {
            return $r->puedeEditarCompras();
        }

        // Solicitante: solo en borrador o rechazada por compras
        if ($r->solicitante_id === $user->id) {
            return in_array($r->estado, ['borrador', 'rechazada_compras']);
        }

        return false;
    }

    /**
     * Revisar una requisición (exclusivo para compras).
     * Solo cuando está en revisión o aprobada_compras (para seguir editando).
     */
    public function revisar(User $user, Requisicion $r): bool
    {
        if (!$user->hasAnyRole(['compras', 'administrador'])) return false;

        return in_array($r->estado, [
            'en_revision_compras',
            'aprobada_compras',
            'en_aprobacion', // compras puede ver aunque ya esté en flujo
        ]);
    }

    /**
     * Aprobar (cuando le toca firmar en el flujo de aprobaciones por monto).
     * Solo aplica desde en_aprobacion — compras ya no aprueba aquí,
     * tiene su propio flujo en RevisarRequisicion.
     */
    public function approve(User $user, Requisicion $r): bool
    {
        if ($r->estado !== 'en_aprobacion') return false;

        return (bool) $r->aprobacionPendientePara($user);
    }

    /**
     * Registrar recepción (solicitante o compras, y ya aprobada_final).
     */
    public function receive(User $user, Requisicion $r): bool
    {
        if ($r->estado !== 'aprobada_final') return false;

        return $r->solicitante_id === $user->id
            || $user->hasAnyRole(['compras', 'administrador']);
    }
}