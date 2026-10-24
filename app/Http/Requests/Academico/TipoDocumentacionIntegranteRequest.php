<?php

namespace App\Http\Requests\Academico;

use App\Models\TipoDocumentacionIntegrante;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TipoDocumentacionIntegranteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $registro = $this->route('tipoDocumentacionIntegrante');

        return $registro instanceof TipoDocumentacionIntegrante
            ? ($this->user()?->can('update', $registro) ?? false)
            : ($this->user()?->can('create', TipoDocumentacionIntegrante::class) ?? false);
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $registro = $this->route('tipoDocumentacionIntegrante');

        return [
            'descripcion' => [
                'required', 'string', 'max:150',
                Rule::unique('tipos_documentacion_integrante', 'descripcion')->ignore($registro instanceof TipoDocumentacionIntegrante ? $registro : null),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'descripcion.unique' => 'Ya existe un registro con esa descripción. Si está inactivo, podés reactivarlo desde el listado.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('descripcion'))) {
            $this->merge(['descripcion' => Str::squish($this->input('descripcion'))]);
        }
    }
}
