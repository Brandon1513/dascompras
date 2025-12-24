<?php
// app/Services/FlujoAprobacionService.php

namespace App\Services;

use App\Models\Aprobacion;
use App\Models\Requisicion;
use App\Models\User;
use App\Notifications\RequisicionPendienteAprobacion;

class FlujoAprobacionService
{
    public function notificarSiguiente(Requisicion $req): void
    {
        // ✅ Siguiente = primera pendiente por orden del nivel
        $pendiente = Aprobacion::query()
            ->where('requisicion_id', $req->id)
            ->where('estado', 'pendiente')
            ->leftJoin('niveles_aprobacion as na', 'na.id', '=', 'aprobaciones.nivel_aprobacion_id')
            ->orderBy('na.orden')
            ->orderBy('aprobaciones.id')
            ->select('aprobaciones.*')
            ->with(['nivel', 'aprobador', 'requisicion.departamentoRef'])
            ->first();

        if (!$pendiente) return;

        // 1) Si viene asignado a un aprobador específico -> notifícalo
        if ($pendiente->aprobador) {
            $pendiente->aprobador->notify(new RequisicionPendienteAprobacion($req, $pendiente));
            return;
        }

        // 2) Si es por rol lógico
        $rolLogico = $pendiente->nivel?->rol_aprobador;
        if (!$rolLogico) return;

        // ✅ Caso especial: gerente_area debe ser el gerente del departamento
        if ($rolLogico === 'gerente_area') {
            $gerenteId = $req->departamentoRef?->gerente_id;

            if ($gerenteId) {
                $gerente = User::find($gerenteId);
                if ($gerente) {
                    $gerente->notify(new RequisicionPendienteAprobacion($req, $pendiente));
                }
            }

            return;
        }

        // ✅ otros roles (gerencia_adm, etc.)
        $roles = $this->mapRolLogico($rolLogico);

        User::role($roles)->get()
            ->each(fn (User $u) => $u->notify(new RequisicionPendienteAprobacion($req, $pendiente)));
    }

    private function mapRolLogico(string $rol): array
    {
        return match ($rol) {
            'gerencia_alta' => ['gerencia_adm', 'gerencia_finanzas'],
            default => [$rol],
        };
    }
}
