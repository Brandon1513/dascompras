<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpedienteArchivo extends Model
{
    protected $fillable = [
        'expediente_id','tipo','nombre_original','extension','tamano',
        'item_id','web_url','subido_por'
    ];

    public function expediente(){ return $this->belongsTo(Expediente::class); }
    public function usuario(){ return $this->belongsTo(User::class,'subido_por'); }
}
