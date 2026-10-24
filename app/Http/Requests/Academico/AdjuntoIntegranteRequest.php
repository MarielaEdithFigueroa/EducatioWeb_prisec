<?php

namespace App\Http\Requests\Academico;

use App\Models\Adjunto;
use App\Models\Integrante;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

class AdjuntoIntegranteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $integrante = $this->route('integrante');

        return $integrante instanceof Integrante
            && $this->user()?->can('update', $integrante) === true
            && ($this->isMethod('post')
                ? $this->user()?->can('create', Adjunto::class) === true
                : $this->user()?->can('update', $this->route('adjunto')) === true);
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        if (! $this->isMethod('post')) {
            return $this->routeIs('academico.integrantes.adjuntos.update')
                ? ['observaciones' => ['present', 'nullable', 'string', 'max:2000']]
                : [];
        }

        return [
            'archivo' => [
                'required',
                File::types(config('adjuntos.extensiones'))->max(config('adjuntos.max_kb')),
                'extensions:'.implode(',', config('adjuntos.extensiones')),
            ],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return list<callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->route('integrante')->activo) {
                $validator->errors()->add('archivo', 'El integrante está dado de baja. Su documentación es de solo consulta.');
            }
        }];
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
