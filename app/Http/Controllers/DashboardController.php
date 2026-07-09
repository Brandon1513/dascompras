<?php

namespace App\Http\Controllers;

use App\Models\Requisicion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user       = Auth::user();
        $esAdmin    = $user->hasAnyRole(['administrador', 'compras']);
        $esJefe     = $user->roles->pluck('name')->map(fn($r) => strtolower($r))->contains('jefe');
        $hoy        = now();
        $inicioMes  = $hoy->copy()->startOfMonth();
        $inicioAnio = $hoy->copy()->startOfYear();

        // ── Base query según rol ───────────────────────────────────────────
        if ($esAdmin) {
            $base = Requisicion::query();
        } elseif ($esJefe) {
            $subordinadosIds = User::where('supervisor_id', $user->id)->pluck('id');
            $misIds = $subordinadosIds->push($user->id);
            $base = Requisicion::whereIn('solicitante_id', $misIds);
        } else {
            $base = Requisicion::where('solicitante_id', $user->id);
        }

        // ── KPIs ──────────────────────────────────────────────────────────
        $totalMes  = (clone $base)->whereDate('fecha_emision', '>=', $inicioMes)->sum('total');
        $totalAnio = (clone $base)->whereDate('fecha_emision', '>=', $inicioAnio)->sum('total');
        $reqsMes   = (clone $base)->whereDate('fecha_emision', '>=', $inicioMes)->count();

        $pendientesAprobacion = (clone $base)->where('estado', 'en_aprobacion')->count();

        $totalMesAnterior = (clone $base)
            ->whereDate('fecha_emision', '>=', $hoy->copy()->subMonth()->startOfMonth())
            ->whereDate('fecha_emision', '<=', $hoy->copy()->subMonth()->endOfMonth())
            ->sum('total');
        $variacion = $totalMesAnterior > 0
            ? (($totalMes - $totalMesAnterior) / $totalMesAnterior) * 100
            : null;

        // ── Estados del mes ───────────────────────────────────────────────
        $estadosMes = (clone $base)
            ->whereDate('fecha_emision', '>=', $inicioMes)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        // ── Gasto 6 meses ─────────────────────────────────────────────────
        $gastoPorMesRaw = (clone $base)
            ->where('fecha_emision', '>=', now()->subMonths(5)->startOfMonth())
            ->whereIn('estado', ['aprobada_final', 'pendiente_cierre', 'recibida'])
            ->select(DB::raw("DATE_FORMAT(fecha_emision, '%Y-%m') as mes"), DB::raw('SUM(total) as total'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');

        $mesesLabels = [];
        $mesesData   = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $mesesLabels[] = $fecha->isoFormat('MMM YY');
            $mesesData[]   = (float) ($gastoPorMesRaw[$fecha->format('Y-m')] ?? 0);
        }

        // ── Gasto por departamento ─────────────────────────────────────────
        $gastoPorDep = collect();
        if ($esAdmin || $esJefe) {
            $gastoPorDep = (clone $base)
                ->whereDate('fecha_emision', '>=', $inicioMes)
                ->whereIn('estado', ['aprobada_final', 'pendiente_cierre', 'recibida'])
                ->join('departamentos', 'requisiciones.departamento_id', '=', 'departamentos.id')
                ->select('departamentos.nombre', DB::raw('SUM(requisiciones.total) as total'))
                ->groupBy('departamentos.nombre')
                ->orderByDesc('total')
                ->limit(5)
                ->get();
        }

        // ── Últimas requisiciones ──────────────────────────────────────────
        $ultimas = (clone $base)
            ->with(['solicitante', 'items'])
            ->latest('id')
            ->limit(5)
            ->get();

        // ── Pendientes de recibir ──────────────────────────────────────────
        $porRecibir = (clone $base)
            ->with('items')
            ->where('estado', 'aprobada_final')
            ->get()
            ->filter(fn($r) => $r->items->count() > 0 && $r->items->every(fn($it) => $it->entregado))
            ->take(5)
            ->values();

        // ── Labels donut ──────────────────────────────────────────────────
        $estadoLabels = [
            'borrador'            => ['Borrador', '#9ca3af'],
            'en_revision_compras' => ['En revisión', '#7c3aed'],
            'rechazada_compras'   => ['Rechazada Compras', '#f97316'],
            'en_aprobacion'       => ['En aprobación', '#f59e0b'],
            'rechazada'           => ['Rechazada', '#ef4444'],
            'aprobada_final'      => ['Aprobada', '#10b981'],
            'pendiente_cierre'    => ['Pend. cierre', '#06b6d4'],
            'recibida'            => ['Recibida', '#6366f1'],
        ];

        $donutLabels = [];
        $donutData   = [];
        $donutColors = [];
        foreach ($estadosMes as $est => $cnt) {
            $donutLabels[] = $estadoLabels[$est][0] ?? $est;
            $donutData[]   = $cnt;
            $donutColors[] = $estadoLabels[$est][1] ?? '#6b7280';
        }

        // ══════════════════════════════════════════════════════════════════
        // FASE 1 — NUEVAS MÉTRICAS
        // ══════════════════════════════════════════════════════════════════

        // ── 1. Panel "Acción requerida" ────────────────────────────────────
        $accionRequerida = collect();

        // Requis que el usuario debe corregir (rechazadas por compras)
        $porCorregir = Requisicion::where('solicitante_id', $user->id)
            ->where('estado', 'rechazada_compras')
            ->with('items')
            ->latest('id')->get()
            ->map(fn($r) => ['requi' => $r, 'accion' => 'corregir', 'urgencia' => 'orange']);

        // Requis que el usuario debe aprobar
        $porAprobar = Requisicion::where('estado', 'en_aprobacion')
            ->whereHas('aprobaciones', fn($q) => $q
                ->where('aprobador_id', $user->id)
                ->where('estado', 'pendiente')
            )
            ->with('items', 'solicitante')
            ->latest('id')->get()
            ->map(fn($r) => ['requi' => $r, 'accion' => 'aprobar', 'urgencia' => 'amber']);

        // Requis listas para recibir
        $porRecibirAccion = $porRecibir->map(fn($r) => ['requi' => $r, 'accion' => 'recibir', 'urgencia' => 'emerald']);

        $accionRequerida = $porCorregir->concat($porAprobar)->concat($porRecibirAccion);

        // ── 2. Top 5 proveedores del mes ──────────────────────────────────
        $topProveedores = collect();
        if ($esAdmin || $esJefe) {
            $topProveedores = DB::table('requisicion_items')
                ->join('requisiciones', 'requisicion_items.requisicion_id', '=', 'requisiciones.id')
                ->whereDate('requisiciones.fecha_emision', '>=', $inicioMes)
                ->whereIn('requisiciones.estado', ['aprobada_final', 'pendiente_cierre', 'recibida'])
                ->whereNotNull('requisicion_items.proveedor_sugerido')
                ->where('requisicion_items.proveedor_sugerido', '!=', '')
                ->when(!$esAdmin && $esJefe, function($q) use ($user) {
                    $ids = User::where('supervisor_id', $user->id)->pluck('id')->push($user->id);
                    $q->whereIn('requisiciones.solicitante_id', $ids);
                })
                ->select(
                    'requisicion_items.proveedor_sugerido as proveedor',
                    DB::raw('SUM(requisicion_items.total_item) as total'),
                    DB::raw('COUNT(DISTINCT requisiciones.id) as num_requis')
                )
                ->groupBy('requisicion_items.proveedor_sugerido')
                ->orderByDesc('total')
                ->limit(5)
                ->get();
        }

        // ── 3. Requisiciones sin movimiento (más de 5 días en aprobación) ─
        $sinMovimiento = (clone $base)
            ->where('estado', 'en_aprobacion')
            ->where('updated_at', '<', now()->subDays(5))
            ->with(['solicitante', 'items'])
            ->latest('updated_at')
            ->limit(5)
            ->get();
        // Top solicitantes del equipo (jefe/admin)
        $topSolicitantes = collect();
        if ($esAdmin || $esJefe) {
            $topSolicitantes = (clone $base)
                ->whereDate('fecha_emision', '>=', $inicioMes)
                ->whereIn('estado', ['aprobada_final', 'pendiente_cierre', 'recibida'])
                ->join('users', 'requisiciones.solicitante_id', '=', 'users.id')
                ->select('users.name as nombre', DB::raw('SUM(requisiciones.total) as total'), DB::raw('COUNT(requisiciones.id) as num_requis'))
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total')
                ->limit(10)
                ->get();
        }    

        return view('dashboard', compact(
            'user', 'esAdmin', 'esJefe', 'hoy',
            'totalMes', 'totalAnio', 'reqsMes',
            'pendientesAprobacion', 'variacion',
            'estadosMes', 'estadoLabels',
            'gastoPorDep', 'ultimas', 'porRecibir',
            'mesesLabels', 'mesesData',
            'donutLabels', 'donutData', 'donutColors',
            // Fase 1
            'accionRequerida', 'topProveedores', 'sinMovimiento', 'topSolicitantes',
        ));
    }
}