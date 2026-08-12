<?php

namespace App\Http\Requests\Academico;

use App\Models\Nivel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NivelRequest extends FormRequest
{
    public function authorize(): bool
    {
        $nivel = $this->route('nivel');

        return $nivel instanceof Nivel
            ? ($this->user()?->can('update', $nivel) ?? false)
            : ($this->user()?->can('create', Nivel::class) ?? false);
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $nivel = $this->route('nivel');

        return [
            'codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('niveles', 'codigo')->ignore($nivel instanceof Nivel ? $nivel : null),
            ],
            'descripcion' => ['required', 'string', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'codigo' => Str::upper(Str::squish((string) $this->input('codigo'))),
            'descripcion' => Str::squish((string) $this->input('descripcion')),
        ]);
    }
}
