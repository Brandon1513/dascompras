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
  .t-center{text-align:center}.t-right{text-align:right}.t-muted{color:#555}
  .badge{display:inline-block;padding:2px 6px;border:1px solid #c9a3ff;border-radius:6px}
  .header{border:1px solid #d1c4e9; padding:8px; border-radius:8px}
  .mt-6{margin-top:6px}.mt-12{margin-top:12px}.mb-6{margin-bottom:6px}
  .sign { height:70px; border:1px dashed #bbb; vertical-align:middle; text-align:center; }
  .title { font-weight:700; font-size:14px; }
  .totals td { border:none; padding:4px 0 }
  .totals .label { text-align:right; padding-right:10px; }
  .purple { background:#f3e8ff }

  /* ✅ Firma en PDF */
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
</style>
</head>
<body>

@php
  // ✅ Ordenar aprobaciones por orden del nivel
  $aps = ($requisicion->aprobaciones ?? collect())
          ->sortBy(fn($a) => $a->nivel?->orden ?? 999)
          ->values();

  $apPorRol = $aps->filter(fn($ap) => !empty($ap->nivel?->rol_aprobador))
                 ->keyBy(fn($ap) => $ap->nivel->rol_aprobador);

  // ✅ Helper: retorna firma + nombre del firmante (si está aprobada)
  $apInfo = function(string $rol) use ($apPorRol) {
      $ap = $apPorRol->get($rol);
      if (!$ap) return null;
      if ($ap->estado !== 'aprobada') return null;

      $nombre = $ap->aprobador?->name
          ?? ($ap->aprobador_id ? ('ID: '.$ap->aprobador_id) : null)
          ?? ('Por rol: ' . ($ap->nivel?->rol_aprobador ?? '—'));

      return [
          'firma'  => $ap->firma_data_uri ?? null, // viene del controller
          'nombre' => $nombre,
      ];
  };

  $jefe  = $apInfo('jefe');
  $area  = $apInfo('gerente_area'); // Gerencia de Área
  $adm   = $apInfo('gerencia_adm'); // Gerencia Administrativa

  // ✅ Firma de recepción (viene del controller como $firmaRecepcionBase64)
  $firmaRecep = $firmaRecepcionBase64 ?? null;

  $fechaRec = $requisicion->fecha_recibido
      ? \Illuminate\Support\Carbon::parse($requisicion->fecha_recibido)->format('d-M-Y')
      : '';

  $nombreAreaRec = trim(($requisicion->recibe_nombre ?? '').' '.(($requisicion->area_recibe ?? '') ? '— '.$requisicion->area_recibe : ''));

  // ✅ Nombre que aparecerá debajo de firma de recepción
  $nombreRecibeFirma = $requisicion->recibe_nombre ?? '';
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
      <th style="width:7%"  class="t-center">Cant.</th>
      <th style="width:20%">Artículo</th>
      <th style="width:8%"  class="t-center">Unidad</th>
      <th style="width:30%">Especificaciones</th>
      <th style="width:15%">Proveedor sugerido</th>
      <th style="width:10%" class="t-right">Precio Unitario</th>
      <th style="width:10%" class="t-right">Subtotal</th>
    </tr>

    @forelse($requisicion->items as $it)
      @php
        $cant = (float) ($it->cantidad ?? 0);
        $pu   = (float) ($it->precio_unitario ?? 0);
        $sub  = !is_null($it->subtotal) ? (float) $it->subtotal : ($cant * $pu);
      @endphp

      <tr>
        <td class="t-center">{{ $it->cantidad }}</td>
        <td>{!! nl2br(e($it->descripcion ?? '')) !!}</td>
        <td class="t-center">{{ $it->unidad ?? '' }}</td>
        <td>
          @if(!empty($it->link_compra))
            <a href="{{ $it->link_compra }}">{{ $it->link_compra }}</a>
          @else
            <span class="t-muted">-</span>
          @endif
        </td>
        <td>{{ $it->proveedor_sugerido ?? '-' }}</td>
        <td class="t-right">${{ number_format($pu, 2) }}</td>
        <td class="t-right">${{ number_format($sub, 2) }}</td>
      </tr>
    @empty
      <tr><td colspan="7" class="t-center t-muted">Sin partidas</td></tr>
    @endforelse
  </table>

  <!-- Totales -->
  <table class="w-100 mt-12">
    <tr>
      <td class="w-50">
        @php $aplicaIva = data_get($requisicion, 'aplica_iva', true); @endphp
        <div class="t-muted">IVA: {{ $aplicaIva ? number_format($ivaRate*100,0).'%' : 'No aplica' }}</div>
      </td>
      <td class="w-50">
        <table class="w-100 totals">
          <tr><td class="label">Subtotal:</td><td class="t-right">${{ number_format($subtotal,2) }}</td></tr>
          @if($iva>0)
            <tr><td class="label">IVA:</td><td class="t-right">${{ number_format($iva,2) }}</td></tr>
          @endif
          <tr><td class="label"><b>Total:</b></td><td class="t-right"><b>${{ number_format($total,2) }}</b></td></tr>
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
      <th class="t-center">Gerencia de Área (de $0.00 hasta $5,000.00)</th>
      <th class="t-center">Gerencia Administrativa (de $5,001 a más)</th>
    </tr>
  </table>

  <!-- ✅ Recepción -->
  <table class="table mt-12">
    <tr class="thead">
      <th class="t-center">Fecha de Recibido</th>
      <th class="t-center">Nombre y área de quien recibe</th>
      <th class="t-center">Firma de conformidad de recepción</th>
    </tr>
    <tr style="height:60px">
      <td class="t-center" style="vertical-align:middle;">
        {{ $fechaRec }}
      </td>

      <td class="t-center" style="vertical-align:middle;">
        {{ $nombreAreaRec ?: '' }}
      </td>

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

</body>
</html>
