<?php

namespace App\Livewire;

use App\Models\Requisicion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public string $periodo   = 'mes_actual';
    public string $fechaDesde = '';
    public string $fechaHasta = '';

    public function mount(): void
    {
        $this->fechaDesde = now()->startOfMonth()->format('Y-m-d');
        $this->fechaHasta = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedPeriodo(): void
    {
        $hoy = now();
        match ($this->periodo) {
            'mes_actual'    => [$this->fechaDesde, $this->fechaHasta] = [$hoy->copy()->startOfMonth()->format('Y-m-d'), $hoy->copy()->endOfMonth()->format('Y-m-d')],
            'mes_anterior'  => [$this->fechaDesde, $this->fechaHasta] = [$hoy->copy()->subMonth()->startOfMonth()->format('Y-m-d'), $hoy->copy()->subMonth()->endOfMonth()->format('Y-m-d')],
            'ultimos_3'     => [$this->fechaDesde, $this->fechaHasta] = [$hoy->copy()->subMonths(3)->startOfMonth()->format('Y-m-d'), $hoy->copy()->endOfMonth()->format('Y-m-d')],
            'ultimos_6'     => [$this->fechaDesde, $this->fechaHasta] = [$hoy->copy()->subMonths(6)->startOfMonth()->format('Y-m-d'), $hoy->copy()->endOfMonth()->format('Y-m-d')],
            'anio_actual'   => [$this->fechaDesde, $this->fechaHasta] = [$hoy->copy()->startOfYear()->format('Y-m-d'), $hoy->copy()->endOfYear()->format('Y-m-d')],
            'personalizado' => null,
            default         => null,
        };
    }

    public function aplicarPersonalizado(): void
    {
        $this->periodo = 'personalizado';
    }

    private function getBase()
    {
        $user   = Auth::user();
        $esAdmin = $user->hasAnyRole(['administrador', 'compras']);
        $esJefe  = $user->roles->pluck('name')->map(fn($r) => strtolower($r))->contains('jefe');

        if ($esAdmin) {
            return Requisicion::query();
        } elseif ($esJefe) {
            $ids = User::where('supervisor_id', $user->id)->pluck('id')->push($user->id);
            return Requisicion::whereIn('solicitante_id', $ids);
        }
        return Requisicion::where('solicitante_id', $user->id);
    }

    public function render()
    {
        $user    = Auth::user();
        $esAdmin = $user->hasAnyRole(['administrador', 'compras']);
        $esJefe  = $user->roles->pluck('name')->map(fn($r) => strtolower($r))->contains('jefe');
        $hoy     = now();

        $desde = $this->fechaDesde ?: now()->startOfMonth()->format('Y-m-d');
        $hasta = $this->fechaHasta ?: now()->endOfMonth()->format('Y-m-d');

        $base = $this->getBase();

        // ── KPIs ──────────────────────────────────────────────────────────
        $totalPeriodo = (clone $base)
            ->whereDate('fecha_emision', '>=', $desde)
            ->whereDate('fecha_emision', '<=', $hasta)
            ->sum('total');

        $totalAnio = (clone $base)
            ->whereDate('fecha_emision', '>=', now()->startOfYear())
            ->sum('total');

        $reqsPeriodo = (clone $base)
            ->whereDate('fecha_emision', '>=', $desde)
            ->whereDate('fecha_emision', '<=', $hasta)
            ->count();

        $pendientesAprobacion = (clone $base)->where('estado', 'en_aprobacion')->count();

        // Comparativa con período anterior de igual duración
        $diasPeriodo   = \Carbon\Carbon::parse($desde)->diffInDays(\Carbon\Carbon::parse($hasta)) + 1;
        $desdeAnterior = \Carbon\Carbon::parse($desde)->subDays($diasPeriodo)->format('Y-m-d');
        $hastaAnterior = \Carbon\Carbon::parse($desde)->subDay()->format('Y-m-d');

        $totalAnterior = (clone $base)
            ->whereDate('fecha_emision', '>=', $desdeAnterior)
            ->whereDate('fecha_emision', '<=', $hastaAnterior)
            ->sum('total');

        $variacion = $totalAnterior > 0
            ? (($totalPeriodo - $totalAnterior) / $totalAnterior) * 100
            : null;

        // ── Estados del período ───────────────────────────────────────────
        $estadosMes = (clone $base)
            ->whereDate('fecha_emision', '>=', $desde)
            ->whereDate('fecha_emision', '<=', $hasta)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        // ── Gasto 6 meses (siempre últimos 6 para la gráfica) ────────────
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
                ->whereDate('fecha_emision', '>=', $desde)
                ->whereDate('fecha_emision', '<=', $hasta)
                ->whereIn('estado', ['aprobada_final', 'pendiente_cierre', 'recibida'])
                ->join('departamentos', 'requisiciones.departamento_id', '=', 'departamentos.id')
                ->select('departamentos.nombre', DB::raw('SUM(requisiciones.total) as total'))
                ->groupBy('departamentos.nombre')
                ->orderByDesc('total')
                ->limit(5)
                ->get();
        }

        // ── Top proveedores ────────────────────────────────────────────────
        $topProveedores = collect();
        if ($esAdmin || $esJefe) {
            $topProveedores = DB::table('requisicion_items')
                ->join('requisiciones', 'requisicion_items.requisicion_id', '=', 'requisiciones.id')
                ->whereDate('requisiciones.fecha_emision', '>=', $desde)
                ->whereDate('requisiciones.fecha_emision', '<=', $hasta)
                ->whereIn('requisiciones.estado', ['aprobada_final', 'pendiente_cierre', 'recibida'])
                ->whereNotNull('requisicion_items.proveedor_sugerido')
                ->where('requisicion_items.proveedor_sugerido', '!=', '')
                ->when(!$esAdmin && $esJefe, function ($q) use ($user) {
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

        // ── Top solicitantes ───────────────────────────────────────────────
        $topSolicitantes = collect();
        if ($esAdmin || $esJefe) {
            $topSolicitantes = (clone $base)
                ->whereDate('fecha_emision', '>=', $desde)
                ->whereDate('fecha_emision', '<=', $hasta)
                ->whereIn('estado', ['aprobada_final', 'pendiente_cierre', 'recibida'])
                ->join('users', 'requisiciones.solicitante_id', '=', 'users.id')
                ->select(
                    'users.name as nombre',
                    DB::raw('SUM(requisiciones.total) as total'),
                    DB::raw('COUNT(requisiciones.id) as num_requis')
                )
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total')
                ->limit(10)
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

        // ── Panel acción requerida ─────────────────────────────────────────
        $porCorregir = Requisicion::where('solicitante_id', $user->id)
            ->where('estado', 'rechazada_compras')
            ->with('items')->latest('id')->get()
            ->map(fn($r) => ['requi' => $r, 'accion' => 'corregir']);

        $porAprobar = Requisicion::where('estado', 'en_aprobacion')
            ->whereHas('aprobaciones', fn($q) => $q->where('aprobador_id', $user->id)->where('estado', 'pendiente'))
            ->with('items', 'solicitante')->latest('id')->get()
            ->map(fn($r) => ['requi' => $r, 'accion' => 'aprobar']);

        $porRecibirAccion = $porRecibir->map(fn($r) => ['requi' => $r, 'accion' => 'recibir']);
        $accionRequerida  = $porCorregir->concat($porAprobar)->concat($porRecibirAccion);

        // ── Sin movimiento ─────────────────────────────────────────────────
        $sinMovimiento = (clone $base)
            ->where('estado', 'en_aprobacion')
            ->where('updated_at', '<', now()->subDays(5))
            ->with(['solicitante', 'items'])
            ->latest('updated_at')
            ->limit(5)
            ->get();

        // ── Donut labels ───────────────────────────────────────────────────
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

        return view('livewire.dashboard', compact(
            'user', 'esAdmin', 'esJefe', 'hoy',
            'desde', 'hasta',
            'totalPeriodo', 'totalAnio', 'reqsPeriodo',
            'pendientesAprobacion', 'variacion',
            'estadosMes', 'estadoLabels',
            'gastoPorDep', 'ultimas', 'porRecibir',
            'mesesLabels', 'mesesData',
            'donutLabels', 'donutData', 'donutColors',
            'accionRequerida', 'topProveedores', 'topSolicitantes', 'sinMovimiento',
        ));
    }
}