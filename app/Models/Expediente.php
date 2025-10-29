<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expediente extends Model
{
    protected $casts = [
    'has_requi'          => 'boolean',
    'has_factura'        => 'boolean',
    'completado_manual'  => 'boolean',
    'completado_en'      => 'datetime',
];
    protected $fillable = [
    'nombre_carpeta','folder_item_id','folder_link','drive_id','folder_path',
    'created_by',
    'has_requi','has_factura','otros_count','estado',
    'completado_manual','completado_por_id','completado_en','completado_nota',
];

    public function archivos() { return $this->hasMany(ExpedienteArchivo::class); }
    public function creador()  { return $this->belongsTo(User::class, 'created_by'); }

    public function getProgresoAttribute(): string {
        $n = (int)$this->has_requi + (int)$this->has_factura + ($this->otros_count > 0 ? 1 : 0);
        return "{$n}/3";
    }
}
