<?php

namespace App\Http\Requests\Academico;

use App\Models\Adjunto;
use App\Models\Aula;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class GuardarFotoAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $aula = $this->route('aula');

        return $aula instanceof Aula
            && $this->user()?->can('update', $aula) === true
            && $this->user()?->can('create', Adjunto::class) === true;
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        return [
            'archivo' => [
                'required',
                File::types(config('adjuntos.extensiones_fotos'))->max(config('adjuntos.max_kb')),
                'extensions:'.implode(',', config('adjuntos.extensiones_fotos')),
            ],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'archivo.required' => 'Seleccioná una foto.',
            'archivo.max' => 'El archivo supera el tamaño máximo permitido.',
            'archivo.mimes' => 'El tipo de archivo no está permitido.',
            'archivo.extensions' => 'La extensión del archivo no está permitida.',
            'archivo.uploaded' => 'No se pudo recibir el archivo. Revisá su tamaño e intentá nuevamente.',
            'observaciones.max' => 'Las observaciones no pueden superar los 2000 caracteres.',
        ];
    }
}
