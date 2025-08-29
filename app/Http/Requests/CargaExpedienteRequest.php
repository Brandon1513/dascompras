<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CargaExpedienteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'carpeta'   => ['required','string','max:200'],
            'requi'     => ['nullable','file','mimes:jpg,jpeg,png,pdf','max:20480'], // 20 MB
            'factura'   => ['nullable','file','mimes:jpg,jpeg,png,pdf','max:20480'],
            'otros'     => ['nullable','array'],
            'otros.*'   => ['file','mimes:jpg,jpeg,png,pdf','max:20480'],
        ];
    }
}
