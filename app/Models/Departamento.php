<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Departamento extends Model
{
    protected $table = 'departamentos';
    protected $fillable = [
        'nombre',
        'gerente_id',
        'activo',
    ];
    protected $casts = [
        'activo' => 'boolean',
    ];

    public function gerente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gerente_id');
    }
}
