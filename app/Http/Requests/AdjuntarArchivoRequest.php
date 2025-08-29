<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjuntarArchivoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'requi'   => ['nullable','file','mimes:jpg,jpeg,png,pdf','max:20480'],
            'factura' => ['nullable','file','mimes:jpg,jpeg,png,pdf','max:20480'],
            'otros'   => ['nullable','array'],
            'otros.*' => ['file','mimes:jpg,jpeg,png,pdf','max:20480'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $hasAny = $this->hasFile('requi')
                   || $this->hasFile('factura')
                   || ($this->file('otros') && count($this->file('otros')) > 0);

            if (!$hasAny) {
                $v->errors()->add('files', 'Debes adjuntar al menos un archivo.');
            }
        });
    }
}
