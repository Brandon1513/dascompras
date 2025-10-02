<?php
// app/Services/FlujoAprobacionService.php

namespace App\Services;

use App\Models\Requisicion;
use App\Models\User;
use App\Models\Aprobacion;
use App\Notifications\RequisicionPendienteAprobacion;

class FlujoAprobacionService
{
    public function notificarSiguiente(Requisicion $req): void
    {
        $pendiente = Aprobacion::where('requisicion_id', $req->id)
            ->where('estado', 'pendiente')
            ->orderBy('created_at')
            ->with('nivel', 'aprobador')
            ->first();

        if (!$pendiente) return;

        if ($pendiente->aprobador) {
            $pendiente->aprobador->notify(new RequisicionPendienteAprobacion($req, $pendiente));
            return;
        }

        // por rol lógico
        $rolLogico = $pendiente->nivel?->rol_aprobador;
        if (!$rolLogico) return;

        $roles = $this->mapRolLogico($rolLogico);
        User::role($roles)->get()
            ->each(fn ($u) => $u->notify(new RequisicionPendienteAprobacion($req, $pendiente)));
    }

    private function mapRolLogico(string $rol): array
    {
        return match ($rol) {
            'gerencia_alta' => ['gerencia_adm', 'gerencia_finanzas'],
            default => [$rol],
        };
    }
}
