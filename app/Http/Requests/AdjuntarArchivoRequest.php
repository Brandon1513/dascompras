<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjuntarArchivoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

   public function rules(): array
{
    return [
        'requi'        => ['nullable','array'],
        'requi.*'      => ['file','mimes:jpg,jpeg,png,pdf','max:5120'],

        'factura'      => ['nullable','array'],
        'factura.*'    => ['file','mimes:jpg,jpeg,png,pdf','max:5120'],

       'recibos'      => ['nullable','array'],
        'recibos.*'    => ['file','mimes:jpg,jpeg,png,pdf','max:20480'],

        // opcional: seguir permitiendo "otros" como alias
        'otros'        => ['nullable','array'],
        'otros.*'      => ['file','mimes:jpg,jpeg,png,pdf','max:20480'],
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