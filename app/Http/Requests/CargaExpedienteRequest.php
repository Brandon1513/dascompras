<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CargaExpedienteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
{
    return [
        'carpeta'      => ['required','string','max:255'],

        // Requisición múltiple
        'requi'        => ['nullable','array'],
        'requi.*'      => ['file','mimes:jpg,jpeg,png,pdf','max:5120'],

        // Factura múltiple
        'factura'      => ['nullable','array'],
        'factura.*'    => ['file','mimes:jpg,jpeg,png,pdf','max:5120'],

        // Otros (ya múltiple)
        'otros'        => ['nullable','array'],
        'otros.*'      => ['file','mimes:jpg,jpeg,png,pdf','max:5120'],
    ];
}
}
