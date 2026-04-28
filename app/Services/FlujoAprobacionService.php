<?php

namespace App\Services;

use App\Models\Aprobacion;
use App\Models\NivelAprobacion;
use App\Models\Requisicion;
use App\Models\User;
use App\Notifications\RequisicionPendienteAprobacion;
use Illuminate\Validation\ValidationException;

class FlujoAprobacionService
{
    /**
     * Notifica al siguiente aprobador pendiente en la cadena.
     */
    public function notificarSiguiente(Requisicion $req): void
    {
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

        // Si hay aprobador específico asignado, notificarlo directamente
        if ($pendiente->aprobador) {
            $pendiente->aprobador->notify(new RequisicionPendienteAprobacion($req, $pendiente));
            return;
        }

        // Notificar por rol
        $rolLogico = $pendiente->nivel?->rol_aprobador;
        if (!$rolLogico) return;

        // Gerente de operaciones: notificar al gerente del departamento
        if ($rolLogico === 'gerente_operaciones') {
            $gerenteId = $req->departamentoRef?->gerente_id;
            if ($gerenteId) {
                $gerente = User::find($gerenteId);
                $gerente?->notify(new RequisicionPendienteAprobacion($req, $pendiente));
            }
            return;
        }

        // Compras: notificar a todos con rol compras
        // Otros roles: notificar por rol
        $roles = $this->mapRolLogico($rolLogico);
        User::role($roles)->get()
            ->each(fn(User $u) => $u->notify(new RequisicionPendienteAprobacion($req, $pendiente)));
    }

    /**
     * Construye la cadena de aprobaciones según el nuevo flujo:
     *
     * Nivel 1 (orden=1): Coordinación de Compras — SIEMPRE (revisión inicial)
     * Nivel 2 (orden=2): Jefe directo            — SIEMPRE (cualquier monto)
     * Nivel 3 (orden=3): Gerente de Operaciones  — si total > $1,000
     * Nivel 4 (orden=4): Gerencia Administrativa — si total > $5,000
     */
    public function crearCadenaAprobacion(Requisicion $req): void
    {
        // Limpiar aprobaciones previas
        $req->aprobaciones()->delete();

        $total = (float) $req->total;

        // ── Nivel 1: Coordinación de Compras (siempre) ───────────────────
        $nivelCompras = NivelAprobacion::where('rol_aprobador', 'compras')
            ->where('activo', true)
            ->first();

        if (!$nivelCompras) {
            throw ValidationException::withMessages([
                'aprobaciones' => 'No existe un nivel activo para Coordinación de Compras.',
            ]);
        }

        // Asignar a cualquier usuario con rol compras
        // (o null para que firme por rol — más flexible)
        Aprobacion::create([
            'requisicion_id'      => $req->id,
            'nivel_aprobacion_id' => $nivelCompras->id,
            'aprobador_id'        => null, // firma por rol compras
            'estado'              => 'pendiente',
        ]);

        // ── Nivel 2: Jefe directo (siempre) ──────────────────────────────
        $nivelJefe = NivelAprobacion::where('rol_aprobador', 'jefe')
            ->where('activo', true)
            ->first();

        if (!$nivelJefe) {
            throw ValidationException::withMessages([
                'aprobaciones' => 'No existe un nivel activo para Jefe directo.',
            ]);
        }

        $jefeId = User::where('id', $req->solicitante_id)->value('supervisor_id');

        if (!$jefeId) {
            throw ValidationException::withMessages([
                'aprobaciones' => 'El solicitante no tiene jefe directo asignado (users.supervisor_id).',
            ]);
        }

        Aprobacion::create([
            'requisicion_id'      => $req->id,
            'nivel_aprobacion_id' => $nivelJefe->id,
            'aprobador_id'        => $jefeId,
            'estado'              => 'pendiente',
        ]);

        // ── Nivel 3: Gerente de Operaciones (si total > $1,000) ──────────
        if ($total > 1000) {
            $nivelGerOp = NivelAprobacion::where('rol_aprobador', 'gerente_operaciones')
                ->where('activo', true)
                ->first();

            if (!$nivelGerOp) {
                throw ValidationException::withMessages([
                    'aprobaciones' => 'No existe un nivel activo para Gerente de Operaciones.',
                ]);
            }

            $req->loadMissing('departamentoRef');
            $gerenteOpId = $req->departamentoRef?->gerente_id;

            if (!$gerenteOpId) {
                throw ValidationException::withMessages([
                    'aprobaciones' => 'El departamento no tiene gerente asignado (departamentos.gerente_id).',
                ]);
            }

            Aprobacion::create([
                'requisicion_id'      => $req->id,
                'nivel_aprobacion_id' => $nivelGerOp->id,
                'aprobador_id'        => $gerenteOpId,
                'estado'              => 'pendiente',
            ]);
        }

        // ── Nivel 4: Gerencia Administrativa (si total > $5,000) ─────────
        if ($total > 5000) {
            $nivelAdm = NivelAprobacion::where('rol_aprobador', 'gerencia_adm')
                ->where('activo', true)
                ->first();

            if (!$nivelAdm) {
                throw ValidationException::withMessages([
                    'aprobaciones' => 'No existe un nivel activo para Gerencia Administrativa.',
                ]);
            }

            Aprobacion::create([
                'requisicion_id'      => $req->id,
                'nivel_aprobacion_id' => $nivelAdm->id,
                'aprobador_id'        => null, // firma por rol gerencia_adm
                'estado'              => 'pendiente',
            ]);
        }
    }

    private function mapRolLogico(string $rol): array
    {
        return match ($rol) {
            'gerencia_alta' => ['gerencia_adm', 'gerencia_finanzas'],
            default         => [$rol],
        };
    }
}