<?php

namespace App\Http\Requests\Academico;

use App\Models\Alumno;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class BuscarResponsableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $alumno = $this->route('alumno');

        return $alumno instanceof Alumno
            && ($this->user()?->can('update', $alumno) ?? false);
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'buscar' => ['required', 'string', 'min:2', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'buscar' => Str::squish((string) $this->input('buscar')),
        ]);
    }

    public function termino(): string
    {
        return $this->validated('buscar');
    }
}
