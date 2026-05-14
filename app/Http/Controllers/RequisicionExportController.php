<?php

namespace App\Http\Controllers;

use App\Models\Requisicion;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RequisicionExportController extends Controller
{
    // ── Colores ───────────────────────────────────────────────────────────
    const COLOR_HEADER_REQ    = '4A1660'; // morado corporativo — columnas de requisición
    const COLOR_HEADER_ITEM   = '6D28D9'; // violeta — columnas de partida
    const COLOR_HEADER_RET    = 'BE123C'; // rojo oscuro — columnas de retención
    const COLOR_ROW_EVEN      = 'F9F5FF'; // fila par suave
    const COLOR_ROW_RET       = 'FFF1F2'; // fila con retenciones

    public function export(Request $request)
    {
        abort_unless(auth()->user()->hasAnyRole(['compras', 'administrador']), 403);

        // ── Filtros ───────────────────────────────────────────────────────
        $desde         = $request->query('desde');
        $hasta         = $request->query('hasta');
        $metodo_pago   = $request->query('metodo_pago');
        $tiene_factura = $request->query('tiene_factura');
        $estado        = $request->query('estado');

        $requisiciones = Requisicion::with([
                'solicitante:id,name',
                'departamentoRef:id,nombre',
                'cerradoPor:id,name',
                'revisadoPor:id,name',
                'items.tipoImpuesto:id,nombre',
                'items.unidadMedida:id,abreviatura',
                'items.retenciones.tipoRetencion:id,nombre,porcentaje',
            ])
            ->when($desde,       fn($q) => $q->whereDate('fecha_emision', '>=', $desde))
            ->when($hasta,       fn($q) => $q->whereDate('fecha_emision', '<=', $hasta))
            ->when($metodo_pago, fn($q) => $q->where('metodo_pago', $metodo_pago))
            ->when($estado,      fn($q) => $q->where('estado', $estado))
            ->when(
                $tiene_factura !== null && $tiene_factura !== '',
                fn($q) => $q->where('tiene_factura', (bool) $tiene_factura)
            )
            ->orderBy('fecha_emision', 'desc')
            ->get();

        // ── Spreadsheet ───────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Requisiciones');

        // ── Definición de columnas ────────────────────────────────────────
        // Grupo A: datos de la requisición
        // Grupo B: datos de la partida
        // Grupo C: retenciones (solo si la requi es pago de factura)
        $headers = [
            // ── Requisición ────────────────────────────────────────────
            'A'  => ['label' => 'Folio',               'grupo' => 'req'],
            'B'  => ['label' => 'Fecha emisión',        'grupo' => 'req'],
            'C'  => ['label' => 'Solicitante',          'grupo' => 'req'],
            'D'  => ['label' => 'Departamento',         'grupo' => 'req'],
            'E'  => ['label' => 'Estado',               'grupo' => 'req'],
            'F'  => ['label' => 'Tipo',                 'grupo' => 'req'],
            'G'  => ['label' => 'Urgencia',             'grupo' => 'req'],
            'H'  => ['label' => 'Método pago general',  'grupo' => 'req'],
            'I'  => ['label' => '¿Tiene factura?',      'grupo' => 'req'],
            'J'  => ['label' => 'UUID / Folio Fiscal',  'grupo' => 'req'],
            'K'  => ['label' => 'Revisado por',         'grupo' => 'req'],
            'L'  => ['label' => 'Fecha revisión',       'grupo' => 'req'],
            'M'  => ['label' => 'Cerrado por',          'grupo' => 'req'],
            'N'  => ['label' => 'Fecha cierre',         'grupo' => 'req'],
            'O'  => ['label' => 'Notas de cierre',      'grupo' => 'req'],
            // ── Partida ────────────────────────────────────────────────
            'P'  => ['label' => '# Partida',            'grupo' => 'item'],
            'Q'  => ['label' => 'Descripción',          'grupo' => 'item'],
            'R'  => ['label' => 'Unidad',               'grupo' => 'item'],
            'S'  => ['label' => 'Cantidad',             'grupo' => 'item'],
            'T'  => ['label' => 'Precio unitario',      'grupo' => 'item'],
            'U'  => ['label' => 'Subtotal partida',     'grupo' => 'item'],
            'V'  => ['label' => 'Impuesto',             'grupo' => 'item'],
            'W'  => ['label' => 'Monto impuesto',       'grupo' => 'item'],
            'X'  => ['label' => 'Total partida',        'grupo' => 'item'],
            'Y'  => ['label' => 'Método pago partida',  'grupo' => 'item'],
            'Z'  => ['label' => 'Proveedor sugerido',   'grupo' => 'item'],
            'AA' => ['label' => 'Link referencia',      'grupo' => 'item'],
            // ── Retenciones ────────────────────────────────────────────
            'AB' => ['label' => 'Retenciones aplicadas','grupo' => 'ret'],
            'AC' => ['label' => 'Monto retenciones',    'grupo' => 'ret'],
            'AD' => ['label' => 'Total neto partida',   'grupo' => 'ret'],
        ];

        // ── Encabezados con color por grupo ──────────────────────────────
        $sheet->getRowDimension(1)->setRowHeight(24);

        $colorPorGrupo = [
            'req'  => self::COLOR_HEADER_REQ,
            'item' => self::COLOR_HEADER_ITEM,
            'ret'  => self::COLOR_HEADER_RET,
        ];

        foreach ($headers as $col => $info) {
            $cell = $col . '1';
            $sheet->setCellValue($cell, $info['label']);
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size'  => 10,
                    'name'  => 'Arial',
                ],
                'fill'      => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $colorPorGrupo[$info['grupo']]],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true,
                ],
                'borders'   => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => 'CCCCCC'],
                    ],
                ],
            ]);
        }

        // ── Filas de datos: una por partida ───────────────────────────────
        $row = 2;
        foreach ($requisiciones as $r) {

            $estadoLabel = Requisicion::ESTADOS_LABEL[$r->estado] ?? $r->estado;

            $tieneFacturaLabel = match($r->tiene_factura) {
                true  => 'Sí',
                false => 'No',
                null  => '—',
            };

            // Datos fijos de la requisición (se repiten en cada partida)
            $datosReq = [
                'A' => $r->folio,
                'B' => optional($r->fecha_emision)->format('d/m/Y'),
                'C' => $r->solicitante?->name ?? '—',
                'D' => $r->departamentoRef?->nombre ?? '—',
                'E' => $estadoLabel,
                'F' => $r->es_pago_factura ? 'Pago de factura' : 'Requisición',
                'G' => $r->urgencia === 'urgente' ? 'Afecta producción' : 'Normal',
                'H' => $r->metodo_pago ? ucfirst($r->metodo_pago) : '—',
                'I' => $tieneFacturaLabel,
                'J' => $r->uuid_factura ?? '—',
                'K' => $r->revisadoPor?->name ?? '—',
                'L' => optional($r->revisado_en)?->format('d/m/Y H:i') ?? '—',
                'M' => $r->cerradoPor?->name ?? '—',
                'N' => optional($r->cerrado_en)?->format('d/m/Y H:i') ?? '—',
                'O' => $r->notas_cierre ?? '—',
            ];

            // Si la requisición no tiene partidas, escribir una fila con —
            if ($r->items->isEmpty()) {
                foreach ($datosReq as $col => $val) {
                    $sheet->setCellValue("{$col}{$row}", $val);
                }
                foreach (['P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD'] as $col) {
                    $sheet->setCellValue("{$col}{$row}", '—');
                }
                $this->aplicarEstiloFila($sheet, $row, array_keys($headers), false, false);
                $row++;
                continue;
            }

            // Una fila por partida
            foreach ($r->items as $i => $it) {

                // Datos de la requisición
                foreach ($datosReq as $col => $val) {
                    $sheet->setCellValue("{$col}{$row}", $val);
                }

                // Unidad
                $unidadLabel = $it->unidadMedida?->abreviatura ?? $it->unidad ?? '—';

                // Retenciones: nombres concatenados
                $retencionesNombres = $it->retenciones->map(function ($ret) {
                    return $ret->tipoRetencion?->nombre ?? '—';
                })->implode(', ');

                $tieneRetenciones = (float)($it->monto_retenciones ?? 0) > 0;

                // Datos de la partida
                $sheet->setCellValue("P{$row}", $i + 1);
                $sheet->setCellValue("Q{$row}", $it->descripcion);
                $sheet->setCellValue("R{$row}", $unidadLabel);
                $sheet->setCellValue("S{$row}", (float) $it->cantidad);
                $sheet->setCellValue("T{$row}", (float) $it->precio_unitario);
                $sheet->setCellValue("U{$row}", (float) $it->subtotal);
                $sheet->setCellValue("V{$row}", $it->tipoImpuesto?->nombre ?? '—');
                $sheet->setCellValue("W{$row}", (float) $it->monto_impuesto);
                $sheet->setCellValue("X{$row}", (float) $it->total_item);
                $sheet->setCellValue("Y{$row}", $it->metodo_pago ? ucfirst($it->metodo_pago) : '—');
                $sheet->setCellValue("Z{$row}", $it->proveedor_sugerido ?? '—');
                $sheet->setCellValue("AA{$row}", $it->link_compra ?? '—');

                // Columnas de retención
                if ($tieneRetenciones) {
                    $sheet->setCellValue("AB{$row}", $retencionesNombres ?: '—');
                    $sheet->setCellValue("AC{$row}", (float) $it->monto_retenciones);
                    $sheet->setCellValue("AD{$row}", (float) $it->total_neto);
                } else {
                    $sheet->setCellValue("AB{$row}", '—');
                    $sheet->setCellValue("AC{$row}", '—');
                    $sheet->setCellValue("AD{$row}", '—');
                }

                // Formato numérico para columnas monetarias
                $moneyCols = ['T', 'U', 'W', 'X'];
                foreach ($moneyCols as $mc) {
                    $sheet->getStyle("{$mc}{$row}")
                        ->getNumberFormat()
                        ->setFormatCode('"$"#,##0.00');
                }
                if ($tieneRetenciones) {
                    $sheet->getStyle("AC{$row}")->getNumberFormat()->setFormatCode('"$"#,##0.00');
                    $sheet->getStyle("AD{$row}")->getNumberFormat()->setFormatCode('"$"#,##0.00');
                }

                // Cantidad como número entero si es entera
                $sheet->getStyle("S{$row}")->getNumberFormat()->setFormatCode('#,##0.##');

                $this->aplicarEstiloFila(
                    $sheet,
                    $row,
                    array_keys($headers),
                    $row % 2 === 0,
                    $tieneRetenciones
                );

                $row++;
            }
        }

        // ── Autosize ──────────────────────────────────────────────────────
        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Limitar ancho máximo para columnas de texto largo
        foreach (['O', 'Q', 'AA', 'AB'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(false);
            $sheet->getColumnDimension($col)->setWidth(35);
            $sheet->getStyle("{$col}2:{$col}{$row}")
                ->getAlignment()->setWrapText(true);
        }

        // ── Freeze primera fila ───────────────────────────────────────────
        $sheet->freezePane('A2');

        // ── Filtros automáticos ───────────────────────────────────────────
        $lastCol = 'AD';
        $sheet->setAutoFilter("A1:{$lastCol}1");

        // ── Descargar ─────────────────────────────────────────────────────
        $filename = 'Requisiciones_Desglosado_' . now()->format('Ymd_His') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(
            fn() => $writer->save('php://output'),
            $filename,
            [
                'Content-Type'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    /**
     * Aplica estilo de fila: alternado y resaltado de retenciones.
     */
    private function aplicarEstiloFila(
        $sheet,
        int $row,
        array $cols,
        bool $esPareja,
        bool $tieneRetenciones
    ): void {
        $last = end($cols);
        $rango = "A{$row}:{$last}{$row}";

        // Fuente base
        $sheet->getStyle($rango)->applyFromArray([
            'font'      => ['name' => 'Arial', 'size' => 9],
            'alignment' => ['vertical' => Alignment::VERTICAL_TOP],
            'borders'   => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'E5E7EB'],
                ],
            ],
        ]);

        // Color fondo alterno
        if ($tieneRetenciones) {
            // Columnas de retención con fondo rosa suave
            $sheet->getStyle("AB{$row}:AD{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF1F2']],
                'font' => ['color' => ['rgb' => '9F1239']],
            ]);
            // Resto de la fila
            $sheet->getStyle("A{$row}:AA{$row}")->applyFromArray([
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $esPareja ? 'F9F5FF' : 'FFFFFF'],
                ],
            ]);
        } else {
            $sheet->getStyle($rango)->applyFromArray([
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $esPareja ? 'F9F5FF' : 'FFFFFF'],
                ],
            ]);
        }

        // Folio en negrita
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);

        // Alineación derecha para columnas numéricas
        foreach (['S','T','U','W','X','AC','AD'] as $col) {
            $sheet->getStyle("{$col}{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // # Partida centrado
        $sheet->getStyle("P{$row}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}