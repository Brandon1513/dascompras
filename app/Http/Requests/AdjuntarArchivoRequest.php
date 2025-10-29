<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjuntarArchivoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

   public function rules(): array
    {
        // Reglas para arrays y para el caso de 1 solo archivo (no array)
        $fileRule = 'file|mimes:jpg,jpeg,png,pdf,heic|max:20480'; // 20 MB

        return [
            // REQUI
            'requi'     => 'sometimes|array',
            'requi.*'   => $fileRule,
            // aceptar también 1 archivo plano (por si llega sin [])
            'requi.0'   => 'sometimes|'.$fileRule,

            // FACTURA
            'factura'   => 'sometimes|array',
            'factura.*' => $fileRule,
            'factura.0' => 'sometimes|'.$fileRule,

            // RECIBOS (¡clave nueva!)
            'recibos'   => 'sometimes|array',
            'recibos.*' => $fileRule,
            'recibos.0' => 'sometimes|'.$fileRule,

            // Compatibilidad: OTROS
            'otros'     => 'sometimes|array',
            'otros.*'   => $fileRule,
            'otros.0'   => 'sometimes|'.$fileRule,
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $hasAny =
                $this->hasFile('requi')   ||
                $this->hasFile('factura') ||
                $this->hasFile('recibos') ||
                $this->hasFile('otros');

            if (!$hasAny) {
                // Mensaje único y claro, igual al que estás viendo
                $v->errors()->add('files', 'Debes adjuntar al menos un archivo.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'requi.*.mimes'   => 'Los REQUI deben ser JPG, PNG, HEIC o PDF.',
            'factura.*.mimes' => 'Las FACTURAS deben ser JPG, PNG, HEIC o PDF.',
            'recibos.*.mimes' => 'Los RECIBOS deben ser JPG, PNG, HEIC o PDF.',
            'otros.*.mimes'   => 'Los archivos deben ser JPG, PNG, HEIC o PDF.',
            'requi.*.max'     => 'Cada archivo REQUI no puede exceder 20 MB.',
            'factura.*.max'   => 'Cada archivo FACTURA no puede exceder 20 MB.',
            'recibos.*.max'   => 'Cada archivo RECIBO no puede exceder 20 MB.',
            'otros.*.max'     => 'Cada archivo no puede exceder 20 MB.',
        ];
    }
    protected function prepareForValidation(): void
{
    // Si llega un solo archivo sin corchetes, envuélvelo a array
    foreach (['requi','factura','recibos','otros'] as $key) {
        if ($this->hasFile($key) && !is_array($this->file($key))) {
            $this->files->set($key, [$this->file($key)]);
        }
    }

    // Mapear 'otros' -> 'recibos' si no viene 'recibos'
    if ($this->hasFile('otros') && !$this->hasFile('recibos')) {
        $this->files->set('recibos', $this->file('otros'));
        $this->files->remove('otros');
    }
}

}