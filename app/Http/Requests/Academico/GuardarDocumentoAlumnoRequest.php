<?php

namespace App\Http\Requests\Academico;

use App\Models\Adjunto;
use App\Models\Alumno;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class GuardarDocumentoAlumnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $alumno = $this->route('alumno');

        return $alumno instanceof Alumno
            && $this->user()?->can('update', $alumno) === true
            && $this->user()?->can('create', Adjunto::class) === true;
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        return [
            'archivo' => [
                'required',
                File::types(config('adjuntos.extensiones'))->max(config('adjuntos.max_kb')),
                'extensions:'.implode(',', config('adjuntos.extensiones')),
            ],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'archivo.required' => 'Seleccioná un archivo.',
            'archivo.max' => 'El archivo supera el tamaño máximo permitido.',
            'archivo.mimes' => 'El tipo de archivo no está permitido.',
            'archivo.extensions' => 'La extensión del archivo no está permitida.',
            'archivo.uploaded' => 'No se pudo recibir el archivo. Revisá su tamaño e intentá nuevamente.',
            'observaciones.max' => 'Las observaciones no pueden superar los 2000 caracteres.',
        ];
    }
}
