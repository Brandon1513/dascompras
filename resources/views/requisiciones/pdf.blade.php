<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Requisición {{ $requisicion->folio }}</title>
<style>
  @page { margin: 22mm 18mm; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; }
  .row { display:flex; }
  .w-100{width:100%}.w-50{width:50%}.w-33{width:33.33%}.w-25{width:25%}
  .table{width:100%; border-collapse:collapse; table-layout:fixed;}
  .table th,.table td{border:1px solid #d1c4e9; padding:6px 8px; vertical-align:top; word-wrap:break-word; overflow-wrap:anywhere;}
  .table a{word-break:break-all; font-size:10px;}
  .partidas th,.partidas td{font-size:11px; padding:5px 6px;}

  .thead{background:#f1e6ff; font-weight:700}
  .thead-ret{background:#fce7f3; font-weight:700; color:#9d174d;}
  .t-center{text-align:center}.t-right{text-align:right}.t-muted{color:#555}
  .badge{display:inline-block;padding:2px 6px;border:1px solid #c9a3ff;border-radius:6px}
  .header{border:1px solid #d1c4e9; padding:8px; border-radius:8px}
  .mt-6{margin-top:6px}.mt-12{margin-top:12px}.mb-6{margin-bottom:6px}
  .sign { height:70px; border:1px dashed #bbb; vertical-align:middle; text-align:center; }
  .title { font-weight:700; font-size:14px; }
  .totals td { border:none; padding:4px 0 }
  .totals .label { text-align:right; padding-right:10px; }
  .purple { background:#f3e8ff }
  .ret-cell { background:#fdf2f8; color:#9d174d; font-size:10px; }
  .ret-total { color:#be123c; font-weight:700; }

  .firma-img{
    max-height:60px;
    max-width:100%;
    display:inline-block;
  }
  .firma-nombre{
    margin-top:4px;
    font-size:10px;
    color:#333;
    line-height:1.1;
  }
  .imp-label{
    font-size:9px;
    color:#777;
    margin-top:2px;
  }
</style>
</head>
<body>

@php
  $aps = ($requisicion->aprobaciones ?? collect())
          ->sortBy(fn($a) => $a->nivel?->orden ?? 999)
          ->values();

  $apPorRol = $aps->filter(fn($ap) => !empty($ap->nivel?->rol_aprobador))
                 ->keyBy(fn($ap) => $ap->nivel->rol_aprobador);

  $apInfo = function(string $rol) use ($apPorRol) {
      $ap = $apPorRol->get($rol);
      if (!$ap || $ap->estado !== 'aprobada') return null;
      $nombre = $ap->aprobador?->name
          ?? ($ap->aprobador_id ? ('ID: '.$ap->aprobador_id) : null)
          ?? ('Por rol: ' . ($ap->nivel?->rol_aprobador ?? '—'));
      return [
          'firma'  => $ap->firma_data_uri ?? null,
          'nombre' => $nombre,
      ];
  };

  $jefe = $apInfo('jefe');
  $area = $apInfo('gerente_operaciones') ?? $apInfo('gerente_area');
  $adm  = $apInfo('gerencia_adm');

  $firmaRecep        = $firmaRecepcionBase64 ?? null;
  $fechaRec          = $requisicion->fecha_recibido
      ? \Illuminate\Support\Carbon::parse($requisicion->fecha_recibido)->format('d-M-Y')
      : '';
  $nombreAreaRec     = trim(($requisicion->recibe_nombre ?? '')
      . (($requisicion->area_recibe ?? '') ? ' — '.$requisicion->area_recibe : ''));
  $nombreRecibeFirma = $requisicion->recibe_nombre ?? '';

  // Retenciones
  $hayRetenciones = $requisicion->es_pago_factura &&
      $requisicion->items->sum(fn($it) => (float)($it->monto_retenciones ?? 0)) > 0;
  $totalRetenciones = $hayRetenciones
      ? $requisicion->items->sum(fn($it) => (float)($it->monto_retenciones ?? 0))
      : 0;
  $totalNeto = $hayRetenciones
      ? $requisicion->items->sum(fn($it) => (float)($it->total_neto ?? $it->total_item ?? 0))
      : (float)$total;
@endphp

  <!-- Encabezado -->
  <table class="table" style="border:1px solid #d1c4e9; margin-bottom:10px">
    <tr>
      <td style="border-right:none; width:110px;">
        @if($logoBase64)
          <img src="{{ $logoBase64 }}" style="height:120px;">
        @endif
      </td>
      <td class="t-center" style="border-left:none;">
        <div class="title">Requisición de Compra</div>
        <div class="t-muted">Folio: <b>{{ $requisicion->folio }}</b></div>
        @if($requisicion->urgencia === 'urgente')
          <div style="margin-top:4px; font-size:11px; color:#c2410c; font-weight:700;">
            ⚠ Afecta proceso de producción
          </div>
        @endif
      </td>
      <td style="width:210px">
        <table class="table">
          <tr><td>Fecha de emisión</td><td class="t-right">{{ optional($requisicion->fecha_emision ?? $requisicion->created_at)->format('d-M-Y') }}</td></tr>
          <tr><td>Revisión</td><td class="t-right">{{ $requisicion->revision ?? '06' }}</td></tr>
          <tr><td>Código</td><td class="t-right">{{ $requisicion->codigo ?? 'F-CCM-03' }}</td></tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- Datos generales -->
  <table class="table">
    <tr class="purple">
      <td>Departamento quien solicita</td>
      <td>Centro de costos (Departamento)</td>
    </tr>
    <tr>
      <td>{{ $requisicion->departamentoRef?->nombre ?? '-' }}</td>
      <td>{{ $requisicion->centroCostoRef?->nombre ?? '-' }}</td>
    </tr>
    <tr class="purple">
      <td>Nombre del solicitante</td>
      <td>Fecha de elaboración</td>
    </tr>
    <tr>
      <td>{{ $requisicion->solicitante->name ?? '-' }}</td>
      <td>{{ optional($requisicion->fecha_emision ?? $requisicion->created_at)->format('d-M-Y') }}</td>
    </tr>
  </table>

  <!-- Partidas -->
  <table class="table mt-12 partidas">
    <tr class="thead">
      <th style="width:6%"  class="t-center">Cant.</th>
      <th style="width:6%"  class="t-center">Unidad</th>
      <th style="width:{{ $hayRetenciones ? '18%' : '22%' }}">Artículo / Descripción</th>
      <th style="width:{{ $hayRetenciones ? '16%' : '22%' }}">Link de referencia</th>
      <th style="width:12%">Proveedor sugerido</th>
      <th style="width:9%" class="t-right">P. Unitario</th>
      <th style="width:9%" class="t-right">Subtotal</th>
      <th style="width:9%" class="t-right">Impuesto(s)</th>
      @if($hayRetenciones)
      <th style="width:9%" class="t-right thead-ret">Retenciones</th>
      @endif
      <th style="width:9%" class="t-right">Total</th>
    </tr>

    @forelse($requisicion->items as $it)
      @php
        $cant      = (int) ($it->cantidad ?? 0);
        $pu        = (float) ($it->precio_unitario ?? 0);
        $sub       = !is_null($it->subtotal) ? (float) $it->subtotal : ($cant * $pu);
        $imp1      = (float) ($it->monto_impuesto ?? 0);
        $imp2      = (float) ($it->monto_impuesto_2 ?? 0);
        $impTotal  = $imp1 + $imp2;
        $totalItem = !is_null($it->total_item) ? (float) $it->total_item : ($sub + $impTotal);
        $montoRet  = (float) ($it->monto_retenciones ?? 0);
        $totalNet  = (float) ($it->total_neto ?? $totalItem);

        $unidadLabel = $it->unidadMedida?->abreviatura ?? $it->unidad ?? '—';
        $imp1Label   = $it->tipoImpuesto?->nombre ?? null;
        $imp2Label   = $it->tipoImpuesto2?->nombre ?? null;

        // Nombres de retenciones de esta partida
        $retencionesNombres = $it->retenciones
            ->map(fn($r) => $r->tipoRetencion?->nombre ?? '')
            ->filter()
            ->implode(', ');

        // Método de pago por partida
        $metodoPago = $it->metodo_pago ?? null;
      @endphp

      <tr>
        <td class="t-center">{{ $cant }}</td>
        <td class="t-center">{{ $unidadLabel }}</td>
        <td>
          {!! nl2br(e($it->descripcion ?? '')) !!}
          @if($metodoPago)
            <div style="margin-top:3px; font-size:9px; color:#5b21b6;">
              {{ match($metodoPago) { 'tarjeta' => '💳', 'transferencia' => '🏦', 'efectivo' => '💵', default => '' } }}
              {{ ucfirst($metodoPago) }}
            </div>
          @endif
        </td>
        <td>
          @if(!empty($it->link_compra))
            <a href="{{ $it->link_compra }}" style="font-size:9px; word-break:break-all;">
              {{ $it->link_compra }}
            </a>
          @else
            <span class="t-muted">—</span>
          @endif
        </td>
        <td>{{ $it->proveedor_sugerido ?? '—' }}</td>
        <td class="t-right">${{ number_format($pu, 2) }}</td>
        <td class="t-right">${{ number_format($sub, 2) }}</td>
        <td class="t-right">
          @if($impTotal > 0)
            ${{ number_format($impTotal, 2) }}
            @if($imp1Label || $imp2Label)
              <div class="imp-label">
                @if($imp1Label && $imp1 > 0){{ $imp1Label }}@endif
                @if($imp1Label && $imp1 > 0 && $imp2Label && $imp2 > 0) + @endif
                @if($imp2Label && $imp2 > 0){{ $imp2Label }}@endif
              </div>
            @endif
          @else
            <span class="t-muted">—</span>
          @endif
        </td>
        @if($hayRetenciones)
        <td class="t-right ret-cell">
          @if($montoRet > 0)
            <span class="ret-total">- ${{ number_format($montoRet, 2) }}</span>
            @if($retencionesNombres)
              <div style="font-size:9px; color:#be123c; margin-top:2px;">{{ $retencionesNombres }}</div>
            @endif
          @else
            <span class="t-muted">—</span>
          @endif
        </td>
        @endif
        <td class="t-right">
          @if($hayRetenciones && $montoRet > 0)
            <b>${{ number_format($totalNet, 2) }}</b>
            <div style="font-size:9px; color:#777; margin-top:2px;">neto</div>
          @else
            <b>${{ number_format($totalItem, 2) }}</b>
          @endif
        </td>
      </tr>
    @empty
      <tr><td colspan="{{ $hayRetenciones ? 10 : 9 }}" class="t-center t-muted">Sin partidas</td></tr>
    @endforelse
  </table>

  <!-- Totales -->
  <table class="w-100 mt-12">
    <tr>
      <td class="w-50">
        @if($requisicion->metodo_pago)
          <div class="t-muted">Método de pago general:
            <b>{{ ucfirst($requisicion->metodo_pago) }}</b>
          </div>
        @endif
        @if($requisicion->es_pago_factura)
          <div class="t-muted" style="margin-top:4px">Tipo: <b>Pago de factura</b></div>
        @endif
      </td>
      <td class="w-50">
        <table class="w-100 totals">
          <tr>
            <td class="label">Subtotal:</td>
            <td class="t-right">${{ number_format($subtotal, 2) }}</td>
          </tr>
          @if($iva > 0)
          <tr>
            <td class="label">Impuestos:</td>
            <td class="t-right">${{ number_format($iva, 2) }}</td>
          </tr>
          @endif
          <tr>
            <td class="label"><b>Total:</b></td>
            <td class="t-right"><b>${{ number_format($total, 2) }}</b></td>
          </tr>
          @if($hayRetenciones)
          <tr>
            <td class="label" style="color:#be123c;">Retenciones:</td>
            <td class="t-right" style="color:#be123c; font-weight:700;">- ${{ number_format($totalRetenciones, 2) }}</td>
          </tr>
          <tr>
            <td class="label"><b>Total neto a pagar:</b></td>
            <td class="t-right"><b style="color:#4A1660; font-size:13px;">${{ number_format($totalNeto, 2) }}</b></td>
          </tr>
          @endif
        </table>
      </td>
    </tr>
  </table>

  <!-- Justificación -->
  <table class="table mt-12">
    <tr class="purple"><td>Justificación de la compra</td></tr>
    <tr><td style="height:60px;">{!! nl2br(e($requisicion->justificacion ?? '')) !!}</td></tr>
  </table>

  <!-- Firmas -->
  <table class="table mt-12">
    <tr class="purple">
      <td>Firma Solicitante</td>
      <td>Jefe Directo</td>
    </tr>
    <tr>
      <td class="sign t-center" style="vertical-align:middle;">
        {{ $requisicion->solicitante->name ?? '-' }}
      </td>
      <td class="sign t-center" style="vertical-align:middle;">
        @if($jefe && !empty($jefe['firma']))
          <img src="{{ $jefe['firma'] }}" class="firma-img">
          <div class="firma-nombre">{{ $jefe['nombre'] }}</div>
        @else
          <span class="t-muted">—</span>
        @endif
      </td>
    </tr>
  </table>

  <!-- Autorizaciones por monto -->
  <table class="table mt-12">
    <tr class="purple">
      <td colspan="2"><b>Autorizaciones por monto de compra</b></td>
    </tr>
    <tr style="height:85px;">
      <td class="t-center" style="vertical-align:middle;">
        @if($area && !empty($area['firma']))
          <img src="{{ $area['firma'] }}" class="firma-img">
          <div class="firma-nombre">{{ $area['nombre'] }}</div>
        @else
          <span class="t-muted">—</span>
        @endif
      </td>
      <td class="t-center" style="vertical-align:middle;">
        @if($adm && !empty($adm['firma']))
          <img src="{{ $adm['firma'] }}" class="firma-img">
          <div class="firma-nombre">{{ $adm['nombre'] }}</div>
        @else
          <span class="t-muted">—</span>
        @endif
      </td>
    </tr>
    <tr class="thead">
      <th class="t-center">Gerente de Operaciones (de $0.00 hasta $5,000.00)</th>
      <th class="t-center">Gerencia Administrativa (de $5,001 a más)</th>
    </tr>
  </table>

  <!-- Recepción -->
  <table class="table mt-12">
    <tr class="thead">
      <th class="t-center">Fecha de Recibido</th>
      <th class="t-center">Nombre y área de quien recibe</th>
      <th class="t-center">Firma de conformidad de recepción</th>
    </tr>
    <tr style="height:60px">
      <td class="t-center" style="vertical-align:middle;">{{ $fechaRec }}</td>
      <td class="t-center" style="vertical-align:middle;">{{ $nombreAreaRec ?: '—' }}</td>
      <td class="t-center" style="vertical-align:middle;">
        @if($firmaRecep)
          <img src="{{ $firmaRecep }}" class="firma-img">
          @if($nombreRecibeFirma)
            <div class="firma-nombre">{{ $nombreRecibeFirma }}</div>
          @endif
        @else
          <span class="t-muted">—</span>
        @endif
      </td>
    </tr>
  </table>

  <!-- Leyendas -->
  <table class="table mt-12">
    <tr>
      <td style="font-size:9px; color:#555; border:none; padding-top:8px;">
        📅 <b>Plazo de entrega:</b> El plazo máximo de entrega de bienes o servicios es de <b>15 días naturales</b>
        a partir de la colocación de la orden de compra, salvo casos especiales informados al solicitante.<br>
        💰 <b>Programación de pagos:</b> Para que el pago se considere en la semana en curso,
        la factura debe recibirse a más tardar el <b>lunes a las 12:00 pm</b>.
      </td>
    </tr>
  </table>

</body>
</html>