<?php

namespace App\Policies;

use App\Models\Requisicion;
use App\Models\User;

class RequisicionPolicy
{
    /** Ver una requisición */
    public function view(User $user, Requisicion $r): bool
    {
        // Dueño
        if ($r->solicitante_id === $user->id) return true;

        // Jefe directo del solicitante (si usas users.supervisor_id)
        if ($r->solicitante && $r->solicitante->supervisor_id === $user->id) return true;

        // Roles con visibilidad total
        if ($user->hasAnyRole(['administrador','compras','gerente_area','gerencia_adm','direccion'])) return true;

        // Aprobadores que les toque esta requisición
        return (bool) $r->aprobacionPendientePara($user);
    }

    /** Editar (solo dueño y en borrador) */
    public function update(User $user, Requisicion $r): bool
    {
        return $r->estado === 'borrador' && $r->solicitante_id === $user->id;
    }

    /** Aprobar (cuando le toca firmar) */
    public function approve(User $user, Requisicion $r): bool
    {
        return $r->estado === 'en_aprobacion' && (bool) $r->aprobacionPendientePara($user);
    }

    /** Registrar recepción (solicitante o compras, y ya aprobada) */
    public function receive(User $user, Requisicion $r): bool
    {
        if ($r->estado !== 'aprobada_final') return false;

        return $r->solicitante_id === $user->id || $user->hasRole('compras');
    }
}
