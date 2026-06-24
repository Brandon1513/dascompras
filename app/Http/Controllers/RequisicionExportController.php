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
    const COLOR_HEADER_REQ    = '4A1660';
    const COLOR_HEADER_ITEM   = '6D28D9';
    const COLOR_HEADER_RET    = 'BE123C';
    const COLOR_ROW_EVEN      = 'F9F5FF';
    const COLOR_ROW_RET       = 'FFF1F2';

    public function export(Request $request)
    {
        abort_unless(auth()->user()->hasAnyRole(['compras', 'administrador']), 403);

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

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Requisiciones');

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
            'K'  => ['label' => 'OC Netsuite',          'grupo' => 'req'],
            'L'  => ['label' => 'Revisado por',         'grupo' => 'req'],
            'M'  => ['label' => 'Fecha revisión',       'grupo' => 'req'],
            'N'  => ['label' => 'Cerrado por',          'grupo' => 'req'],
            'O'  => ['label' => 'Fecha cierre',         'grupo' => 'req'],
            'P'  => ['label' => 'Notas de cierre',      'grupo' => 'req'],
            // ── Partida ────────────────────────────────────────────────
            'Q'  => ['label' => '# Partida',            'grupo' => 'item'],
            'R'  => ['label' => 'Descripción',          'grupo' => 'item'],
            'S'  => ['label' => 'Unidad',               'grupo' => 'item'],
            'T'  => ['label' => 'Cantidad',             'grupo' => 'item'],
            'U'  => ['label' => 'Precio unitario',      'grupo' => 'item'],
            'V'  => ['label' => 'Subtotal partida',     'grupo' => 'item'],
            'W'  => ['label' => 'Impuesto',             'grupo' => 'item'],
            'X'  => ['label' => 'Monto impuesto',       'grupo' => 'item'],
            'Y'  => ['label' => 'Total partida',        'grupo' => 'item'],
            'Z'  => ['label' => 'Método pago partida',  'grupo' => 'item'],
            'AA' => ['label' => 'Proveedor sugerido',   'grupo' => 'item'],
            'AB' => ['label' => 'Link referencia',      'grupo' => 'item'],
            // ── Retenciones ────────────────────────────────────────────
            'AC' => ['label' => 'Retenciones aplicadas','grupo' => 'ret'],
            'AD' => ['label' => 'Monto retenciones',    'grupo' => 'ret'],
            'AE' => ['label' => 'Total neto partida',   'grupo' => 'ret'],
        ];

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

        $row = 2;
        foreach ($requisiciones as $r) {

            $estadoLabel = Requisicion::ESTADOS_LABEL[$r->estado] ?? $r->estado;

            $tieneFacturaLabel = match($r->tiene_factura) {
                true  => 'Sí',
                false => 'No',
                null  => '—',
            };

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
                'K' => $r->oc_netsuite ?? '—',
                'L' => $r->revisadoPor?->name ?? '—',
                'M' => optional($r->revisado_en)?->format('d/m/Y H:i') ?? '—',
                'N' => $r->cerradoPor?->name ?? '—',
                'O' => optional($r->cerrado_en)?->format('d/m/Y H:i') ?? '—',
                'P' => $r->notas_cierre ?? '—',
            ];

            if ($r->items->isEmpty()) {
                foreach ($datosReq as $col => $val) {
                    $sheet->setCellValue("{$col}{$row}", $val);
                }
                foreach (['Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE'] as $col) {
                    $sheet->setCellValue("{$col}{$row}", '—');
                }
                $this->aplicarEstiloFila($sheet, $row, array_keys($headers), false, false);
                $row++;
                continue;
            }

            foreach ($r->items as $i => $it) {

                foreach ($datosReq as $col => $val) {
                    $sheet->setCellValue("{$col}{$row}", $val);
                }

                $unidadLabel = $it->unidadMedida?->abreviatura ?? $it->unidad ?? '—';

                $retencionesNombres = $it->retenciones->map(function ($ret) {
                    return $ret->tipoRetencion?->nombre ?? '—';
                })->implode(', ');

                $tieneRetenciones = (float)($it->monto_retenciones ?? 0) > 0;

                $sheet->setCellValue("Q{$row}", $i + 1);
                $sheet->setCellValue("R{$row}", $it->descripcion);
                $sheet->setCellValue("S{$row}", $unidadLabel);
                $sheet->setCellValue("T{$row}", (float) $it->cantidad);
                $sheet->setCellValue("U{$row}", (float) $it->precio_unitario);
                $sheet->setCellValue("V{$row}", (float) $it->subtotal);
                $sheet->setCellValue("W{$row}", $it->tipoImpuesto?->nombre ?? '—');
                $sheet->setCellValue("X{$row}", (float) $it->monto_impuesto);
                $sheet->setCellValue("Y{$row}", (float) $it->total_item);
                $sheet->setCellValue("Z{$row}", $it->metodo_pago ? ucfirst($it->metodo_pago) : '—');
                $sheet->setCellValue("AA{$row}", $it->proveedor_sugerido ?? '—');
                $sheet->setCellValue("AB{$row}", $it->link_compra ?? '—');

                if ($tieneRetenciones) {
                    $sheet->setCellValue("AC{$row}", $retencionesNombres ?: '—');
                    $sheet->setCellValue("AD{$row}", (float) $it->monto_retenciones);
                    $sheet->setCellValue("AE{$row}", (float) $it->total_neto);
                } else {
                    $sheet->setCellValue("AC{$row}", '—');
                    $sheet->setCellValue("AD{$row}", '—');
                    $sheet->setCellValue("AE{$row}", '—');
                }

                $moneyCols = ['U', 'V', 'X', 'Y'];
                foreach ($moneyCols as $mc) {
                    $sheet->getStyle("{$mc}{$row}")
                        ->getNumberFormat()
                        ->setFormatCode('"$"#,##0.00');
                }
                if ($tieneRetenciones) {
                    $sheet->getStyle("AD{$row}")->getNumberFormat()->setFormatCode('"$"#,##0.00');
                    $sheet->getStyle("AE{$row}")->getNumberFormat()->setFormatCode('"$"#,##0.00');
                }

                $sheet->getStyle("T{$row}")->getNumberFormat()->setFormatCode('#,##0.##');

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

        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        foreach (['P', 'R', 'AB', 'AC'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(false);
            $sheet->getColumnDimension($col)->setWidth(35);
            $sheet->getStyle("{$col}2:{$col}{$row}")
                ->getAlignment()->setWrapText(true);
        }

        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:AE1");

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

    private function aplicarEstiloFila(
        $sheet,
        int $row,
        array $cols,
        bool $esPareja,
        bool $tieneRetenciones
    ): void {
        $last  = end($cols);
        $rango = "A{$row}:{$last}{$row}";

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

        if ($tieneRetenciones) {
            $sheet->getStyle("AC{$row}:AE{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF1F2']],
                'font' => ['color' => ['rgb' => '9F1239']],
            ]);
            $sheet->getStyle("A{$row}:AB{$row}")->applyFromArray([
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

        $sheet->getStyle("A{$row}")->getFont()->setBold(true);

        foreach (['T','U','V','X','Y','AD','AE'] as $col) {
            $sheet->getStyle("{$col}{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        $sheet->getStyle("Q{$row}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}