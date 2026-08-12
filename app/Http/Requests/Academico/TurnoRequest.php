<?php

namespace App\Http\Requests\Academico;

use App\Models\Turno;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TurnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $turno = $this->route('turno');

        return $turno instanceof Turno
            ? ($this->user()?->can('update', $turno) ?? false)
            : ($this->user()?->can('create', Turno::class) ?? false);
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $turno = $this->route('turno');

        return [
            'descripcion' => [
                'required',
                'string',
                'max:50',
                Rule::unique('turnos', 'descripcion')->ignore($turno instanceof Turno ? $turno : null),
            ],
            'orden' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'descripcion' => Str::squish((string) $this->input('descripcion')),
        ]);
    }
}
