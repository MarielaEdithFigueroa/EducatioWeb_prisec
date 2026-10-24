<?php

namespace App\Http\Requests\Academico;

use App\Models\Aula;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $aula = $this->route('aula');

        return $aula instanceof Aula
            ? ($this->user()?->can('update', $aula) ?? false)
            : ($this->user()?->can('create', Aula::class) ?? false);
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $aula = $this->route('aula');

        return [
            'descripcion' => ['required', 'string', 'max:100', Rule::unique('aulas', 'descripcion')->ignore($aula instanceof Aula ? $aula : null)],
            'descripcion_larga' => ['nullable', 'string', 'max:2000'],
            'capacidad' => ['nullable', 'integer', 'between:1,65535'],
            'es_especial' => ['required', 'boolean'],
            'especialidad' => [Rule::requiredIf($this->boolean('es_especial')), 'nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['descripcion', 'especialidad'] as $campo) {
            if (is_string($this->input($campo))) {
                $this->merge([$campo => Str::squish($this->input($campo))]);
            }
        }

        if (! $this->boolean('es_especial')) {
            $this->merge(['especialidad' => null]);
        }
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'descripcion.unique' => 'Ya existe un aula con esa descripción. Si está inactiva, podés reactivarla desde el listado.',
            'especialidad.required' => 'Indicá la especialidad del aula, por ejemplo Música.',
            'capacidad.between' => 'La capacidad debe estar entre 1 y 65535 personas.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['descripcion' => 'descripción corta', 'descripcion_larga' => 'descripción larga', 'es_especial' => 'aula especial'];
    }

    /** @return array<string, mixed> */
    public function datos(): array
    {
        return [
            ...$this->validated(),
            'descripcion_larga' => $this->validated('descripcion_larga'),
            'capacidad' => $this->validated('capacidad'),
            'especialidad' => $this->validated('especialidad'),
        ];
    }
}
