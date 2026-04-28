<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class RequisicionItemArchivo extends Model
{
    protected $table = 'requisicion_item_archivos';

    protected $fillable = [
        'requisicion_item_id',
        'tipo',
        'nombre_original',
        'path',
        'mime_type',
        'tamanio',
        'subido_por_id',
    ];

    // Etiquetas legibles para los tipos
    const TIPOS = [
        'ficha_tecnica' => 'Ficha Técnica',
        'cotizacion'    => 'Cotización',
        'otro'          => 'Otro',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(RequisicionItem::class, 'requisicion_item_id');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por_id');
    }

    // URL pública del archivo
    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    // Etiqueta legible del tipo
    public function getTipoLabelAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    // Tamaño en formato legible (KB, MB)
    public function getTamanioFormateadoAttribute(): string
    {
        if (!$this->tamanio) return '—';

        if ($this->tamanio >= 1048576) {
            return round($this->tamanio / 1048576, 2) . ' MB';
        }
        return round($this->tamanio / 1024, 1) . ' KB';
    }

    // Icono según tipo MIME (para mostrar en vistas)
    public function getIconoAttribute(): string
    {
        return match(true) {
            str_contains($this->mime_type ?? '', 'pdf')   => '📄',
            str_contains($this->mime_type ?? '', 'image') => '🖼️',
            str_contains($this->mime_type ?? '', 'sheet') => '📊',
            str_contains($this->mime_type ?? '', 'word')  => '📝',
            default                                        => '📎',
        };
    }
}