<?php

namespace App\Policies;

use App\Models\Expediente;
use App\Models\User;

class ExpedientePolicy
{
    /**
     * Quién puede marcar un expediente como completado manualmente.
     */
    public function marcarCompletado(User $user, Expediente $e): bool
{
    if ($user->hasAnyRole(['administrador', 'compras'])) return true;

    // dueño del expediente
    return (int)($e->created_by ?? 0) === (int)$user->id;
}

public function desmarcarCompletado(User $user, Expediente $e): bool
{
    if ($user->hasAnyRole(['administrador', 'compras'])) return true;

    // sólo quien lo marcó puede revertir
    return (int)($e->completado_por_id ?? 0) === (int)$user->id;
}
}
