<?php

namespace App\Http\Controllers;

use App\Models\Requisicion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RequisicionExportController extends Controller
{
    public function export(Request $request)
    {
        abort_unless(auth()->user()->hasAnyRole(['compras', 'administrador']), 403);

        // ── Filtros opcionales ────────────────────────────────────────────
        $desde       = $request->query('desde');
        $hasta       = $request->query('hasta');
        $metodo_pago = $request->query('metodo_pago');
        $tiene_factura = $request->query('tiene_factura');
        $estado      = $request->query('estado');

        $requisiciones = Requisicion::with([
                'solicitante:id,name',
                'departamentoRef:id,nombre',
                'cerradoPor:id,name',
                'revisadoPor:id,name',
            ])
            ->when($desde, fn($q) => $q->whereDate('fecha_emision', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('fecha_emision', '<=', $hasta))
            ->when($metodo_pago, fn($q) => $q->where('metodo_pago', $metodo_pago))
            ->when($estado, fn($q) => $q->where('estado', $estado))
            ->when($tiene_factura !== null && $tiene_factura !== '',
                fn($q) => $q->where('tiene_factura', (bool) $tiene_factura)
            )
            ->orderBy('fecha_emision', 'desc')
            ->get();

        // ── Crear Excel ───────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Requisiciones');

        // Columnas
        $headers = [
            'A' => 'Folio',
            'B' => 'Fecha emisión',
            'C' => 'Solicitante',
            'D' => 'Departamento',
            'E' => 'Estado',
            'F' => 'Tipo',
            'G' => 'Urgencia',
            'H' => 'Método de pago',
            'I' => 'Subtotal',
            'J' => 'Impuestos',
            'K' => 'Total',
            'L' => '¿Tiene factura?',
            'M' => 'UUID / Folio Fiscal',
            'N' => 'Factura (archivo)',
            'O' => 'Revisado por (Compras)',
            'P' => 'Fecha revisión',
            'Q' => 'Cerrado por',
            'R' => 'Fecha cierre',
            'S' => 'Notas de cierre',
        ];

        // ── Estilo de encabezado ──────────────────────────────────────────
        $sheet->getRowDimension(1)->setRowHeight(22);

        foreach ($headers as $col => $label) {
            $cell = $col . '1';
            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4A1660']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
            ]);
        }

        // ── Datos ─────────────────────────────────────────────────────────
        $row = 2;
        foreach ($requisiciones as $r) {

            $estadoLabel = Requisicion::ESTADOS_LABEL[$r->estado] ?? $r->estado;

            $tieneFacturaLabel = match($r->tiene_factura) {
                true  => 'Sí',
                false => 'No',
                null  => 'Sin definir',
            };

            $sheet->setCellValue("A{$row}", $r->folio);
            $sheet->setCellValue("B{$row}", optional($r->fecha_emision)->format('d/m/Y'));
            $sheet->setCellValue("C{$row}", $r->solicitante?->name ?? '—');
            $sheet->setCellValue("D{$row}", $r->departamentoRef?->nombre ?? '—');
            $sheet->setCellValue("E{$row}", $estadoLabel);
            $sheet->setCellValue("F{$row}", $r->es_pago_factura ? 'Pago de factura' : 'Requisición');
            $sheet->setCellValue("G{$row}", ucfirst($r->urgencia));
            $sheet->setCellValue("H{$row}", $r->metodo_pago ? ucfirst($r->metodo_pago) : '—');
            $sheet->setCellValue("I{$row}", (float) $r->subtotal);
            $sheet->setCellValue("J{$row}", (float) $r->iva);
            $sheet->setCellValue("K{$row}", (float) $r->total);
            $sheet->setCellValue("L{$row}", $tieneFacturaLabel);
            $sheet->setCellValue("M{$row}", $r->uuid_factura ?? '—');
            $sheet->setCellValue("N{$row}", $r->factura_compras_nombre ?? ($r->factura_nombre ?? '—'));
            $sheet->setCellValue("O{$row}", $r->revisadoPor?->name ?? '—');
            $sheet->setCellValue("P{$row}", optional($r->revisado_en)->format('d/m/Y H:i') ?? '—');
            $sheet->setCellValue("Q{$row}", $r->cerradoPor?->name ?? '—');
            $sheet->setCellValue("R{$row}", optional($r->cerrado_en)->format('d/m/Y H:i') ?? '—');
            $sheet->setCellValue("S{$row}", $r->notas_cierre ?? '—');

            // Fila alterna
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:S{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9F5FF']],
                ]);
            }

            // Formato numérico para totales
            $sheet->getStyle("I{$row}:K{$row}")->getNumberFormat()
                ->setFormatCode('"$"#,##0.00');

            $row++;
        }

        // ── Autosize columnas ─────────────────────────────────────────────
        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ── Freeze primera fila ───────────────────────────────────────────
        $sheet->freezePane('A2');

        // ── Devolver como descarga ────────────────────────────────────────
        $filename = 'Requisiciones_' . now()->format('Ymd_His') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            fn() => $writer->save('php://output'),
            $filename,
            [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control'       => 'max-age=0',
            ]
        );
    }
}