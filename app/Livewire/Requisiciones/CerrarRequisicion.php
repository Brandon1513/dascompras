<?php

namespace App\Livewire\Requisiciones;

use App\Models\Requisicion;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Smalot\PdfParser\Parser as PdfParser;

#[Layout('layouts.app')]
class CerrarRequisicion extends Component
{
    use AuthorizesRequests, WithFileUploads;

    public Requisicion $requisicion;

    // ── Campos del cierre ─────────────────────────────────────────────────
    public ?bool   $tiene_factura          = null;
    public mixed   $factura_compras_nueva  = null;
    public ?string $factura_compras_path   = null;
    public ?string $factura_compras_nombre = null;
    public string  $uuid_factura           = '';
    public string  $notas_cierre           = '';

    // Indica si la factura viene de la requisición original (pago de factura)
    public bool $facturaEsDelSolicitante = false;

    // Control UI
    public bool   $extrayendoUuid    = false;
    public string $mensajeExtraccion = '';
    public bool   $uuidAutoDetectado = false;

    public function mount(Requisicion $requisicion): void
    {
        abort_unless(Auth::user()->hasAnyRole(['compras', 'administrador']), 403);
        abort_unless($requisicion->estado === 'pendiente_cierre', 403);

        $requisicion->load([
            'solicitante',
            'departamentoRef',
            'items.tipoImpuesto',
            'aprobaciones.nivel',
            'aprobaciones.aprobador',
            'items.retenciones.tipoRetencion',
        ]);

        $this->requisicion  = $requisicion;
        $this->notas_cierre = $requisicion->notas_cierre ?? '';
        $this->uuid_factura = $requisicion->uuid_factura ?? '';

        // ── Si ya tiene datos de cierre previos (guardado parcial) ────────
        if ($requisicion->factura_compras_path) {
            $this->factura_compras_path   = $requisicion->factura_compras_path;
            $this->factura_compras_nombre = $requisicion->factura_compras_nombre;
            $this->tiene_factura          = $requisicion->tiene_factura;

        // ── Si es pago de factura, reutilizar la factura del solicitante ──
        } elseif ($requisicion->es_pago_factura && $requisicion->factura_path) {
            $this->factura_compras_path   = $requisicion->factura_path;
            $this->factura_compras_nombre = $requisicion->factura_nombre;
            $this->tiene_factura          = true;
            $this->facturaEsDelSolicitante = true;

            // Intentar extraer UUID si aún no se tiene
            if (empty($this->uuid_factura)) {
                $this->intentarExtraerUuid($requisicion->factura_path);
            }

        } else {
            $this->tiene_factura = $requisicion->tiene_factura;
        }
    }

    public function render()
    {
        return view('livewire.requisiciones.cerrar-requisicion');
    }

    // ── Extracción automática al subir un PDF nuevo ────────────────────────

    public function updatedFacturaComprasNueva(): void
    {
        if (!$this->factura_compras_nueva) return;

        $mime = $this->factura_compras_nueva->getMimeType();
        if (!str_contains($mime, 'pdf')) {
            $this->mensajeExtraccion = '';
            return;
        }

        try {
            $uuid = $this->extraerUuidDePdf(
                $this->factura_compras_nueva->getRealPath()
            );

            if ($uuid) {
                $this->uuid_factura      = $uuid;
                $this->uuidAutoDetectado = true;
                $this->mensajeExtraccion = '✅ UUID detectado automáticamente del PDF.';
            } else {
                $this->uuidAutoDetectado = false;
                $this->mensajeExtraccion = 'ℹ️ No se pudo extraer el UUID. Ingrésalo manualmente.';
            }
        } catch (\Exception $e) {
            \Log::warning('CerrarRequisicion: error extrayendo UUID: ' . $e->getMessage());
            $this->uuidAutoDetectado = false;
            $this->mensajeExtraccion = 'ℹ️ No se pudo leer el PDF. Ingresa el UUID manualmente.';
        }
    }

    // ── Intentar extraer UUID de una factura ya guardada en storage ────────

    private function intentarExtraerUuid(string $storagePath): void
    {
        try {
            $fullPath = Storage::disk('public')->path($storagePath);

            if (!file_exists($fullPath)) return;

            // Solo intentar con PDFs
            $mime = mime_content_type($fullPath);
            if (!str_contains($mime, 'pdf')) return;

            $uuid = $this->extraerUuidDePdf($fullPath);

            if ($uuid) {
                $this->uuid_factura      = $uuid;
                $this->uuidAutoDetectado = true;
                $this->mensajeExtraccion = '✅ UUID detectado automáticamente de la factura adjunta.';
            } else {
                $this->mensajeExtraccion = 'ℹ️ No se encontró el UUID en el PDF. Ingrésalo manualmente.';
            }
        } catch (\Exception $e) {
            \Log::warning('CerrarRequisicion mount UUID: ' . $e->getMessage());
            $this->mensajeExtraccion = 'ℹ️ No se pudo leer el PDF. Ingresa el UUID manualmente.';
        }
    }

    /**
     * Extrae el UUID (Folio Fiscal CFDI) de un PDF de factura electrónica.
     */
    private function extraerUuidDePdf(string $rutaPdf): ?string
    {
        $parser = new PdfParser();
        $pdf    = $parser->parseFile($rutaPdf);
        $texto  = $pdf->getText();

        $patron = '/[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}/';
        preg_match_all($patron, $texto, $matches);

        if (empty($matches[0])) return null;

        // Buscar por contexto (palabras clave de facturas mexicanas CFDI)
        $textoBajo = strtolower($texto);
        $claves    = ['folio fiscal', 'uuid', 'folio digital', 'timbre fiscal'];

        foreach ($claves as $clave) {
            $pos = strpos($textoBajo, $clave);
            if ($pos === false) continue;

            foreach ($matches[0] as $uuid) {
                $posUuid = strpos(strtolower($texto), strtolower($uuid));
                if ($posUuid !== false && $posUuid > $pos && ($posUuid - $pos) < 300) {
                    return strtoupper($uuid);
                }
            }
        }

        return strtoupper($matches[0][0]);
    }

    // ── Eliminar factura de compras guardada ───────────────────────────────

    public function removeFacturaCompras(): void
    {
        // Si la factura es del solicitante, no borrar el archivo original
        if (!$this->facturaEsDelSolicitante && $this->factura_compras_path) {
            Storage::disk('public')->delete($this->factura_compras_path);
        }

        $this->factura_compras_path    = null;
        $this->factura_compras_nombre  = null;
        $this->factura_compras_nueva   = null;
        $this->uuid_factura            = '';
        $this->uuidAutoDetectado       = false;
        $this->mensajeExtraccion       = '';
        $this->facturaEsDelSolicitante = false;

        // Limpiar en BD solo si había una factura de compras (no la del solicitante)
        if ($this->requisicion->factura_compras_path) {
            $this->requisicion->update([
                'factura_compras_path'   => null,
                'factura_compras_nombre' => null,
                'uuid_factura'           => null,
            ]);
        }
    }

    // ── Validaciones ──────────────────────────────────────────────────────

    private function rulesConFactura(): array
    {
        // Si ya hay factura (del solicitante o guardada), no es obligatorio subir nueva
        $facturaRequerida = !$this->factura_compras_path;

        return [
            'tiene_factura'         => ['required', 'boolean'],
            'uuid_factura'          => [
                'nullable', 'string', 'max:36',
                'regex:/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/i',
            ],
            'factura_compras_nueva' => array_merge(
                $facturaRequerida ? ['required'] : ['nullable'],
                ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png']
            ),
            'notas_cierre'          => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function rulesSinFactura(): array
    {
        return [
            'tiene_factura' => ['required', 'boolean'],
            'notas_cierre'  => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function messages(): array
    {
        return [
            'factura_compras_nueva.required' => 'Debes adjuntar la factura para cerrar.',
            'factura_compras_nueva.mimes'    => 'La factura debe ser PDF, JPG o PNG.',
            'factura_compras_nueva.max'      => 'La factura no debe superar 10 MB.',
            'uuid_factura.regex'             => 'El UUID debe tener el formato: XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
        ];
    }

    // ── Cerrar CON factura ────────────────────────────────────────────────

    public function cerrarConFactura(): void
    {
        $this->tiene_factura = true;
        $this->validate($this->rulesConFactura(), $this->messages());

        DB::transaction(function () {
            $path   = $this->factura_compras_path;
            $nombre = $this->factura_compras_nombre;

            // Solo guardar nueva ruta si se subió un archivo nuevo
            if ($this->factura_compras_nueva) {
                // Borrar solo si hay una factura de compras previa (no la del solicitante)
                if ($path && !$this->facturaEsDelSolicitante) {
                    Storage::disk('public')->delete($path);
                }
                $nombre = $this->factura_compras_nueva->getClientOriginalName();
                $path   = $this->factura_compras_nueva->store('requisiciones/facturas_compras', 'public');
            }

            $this->requisicion->update([
                'estado'                 => 'recibida',
                'tiene_factura'          => true,
                'uuid_factura'           => $this->uuid_factura ?: null,
                'factura_compras_path'   => $path,
                'factura_compras_nombre' => $nombre,
                'notas_cierre'           => $this->notas_cierre ?: null,
                'cerrado_por_id'         => Auth::id(),
                'cerrado_en'             => now(),
            ]);
        });

        session()->flash('status', 'Requisición cerrada correctamente con factura adjunta.');
        $this->js("window.location.href = '" . route('requisiciones.index') . "'");
    }

    // ── Cerrar SIN factura ────────────────────────────────────────────────

    public function cerrarSinFactura(): void
    {
        $this->tiene_factura = false;
        $this->validate($this->rulesSinFactura(), $this->messages());

        DB::transaction(function () {
            $this->requisicion->update([
                'estado'         => 'recibida',
                'tiene_factura'  => false,
                'uuid_factura'   => null,
                'notas_cierre'   => $this->notas_cierre ?: null,
                'cerrado_por_id' => Auth::id(),
                'cerrado_en'     => now(),
            ]);
        });

        session()->flash('status', 'Requisición cerrada sin factura. Se registró para el reporte.');
        $this->js("window.location.href = '" . route('requisiciones.index') . "'");
    }
}