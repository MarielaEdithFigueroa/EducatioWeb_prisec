<?php

namespace App\Http\Requests\Academico;

use App\Models\Division;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $division = $this->route('division');

        return $division instanceof Division
            ? ($this->user()?->can('update', $division) ?? false)
            : ($this->user()?->can('create', Division::class) ?? false);
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $division = $this->route('division');

        return [
            'descripcion' => [
                'required',
                'string',
                'max:50',
                Rule::unique('divisiones', 'descripcion')->ignore($division instanceof Division ? $division : null),
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
