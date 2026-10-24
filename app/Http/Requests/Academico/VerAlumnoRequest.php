<?php

namespace App\Http\Requests\Academico;

use App\Models\Alumno;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerAlumnoRequest extends FormRequest
{
    /** @var array<int, string> */
    private const SECCIONES = ['personal', 'institucional', 'responsables', 'documentacion'];

    protected function prepareForValidation(): void
    {
        $seccion = $this->query('seccion');

        if (! is_string($seccion) || ! in_array($seccion, self::SECCIONES, true)) {
            $this->merge(['seccion' => 'personal']);
        }
    }

    public function authorize(): bool
    {
        $alumno = $this->route('alumno');

        return $alumno instanceof Alumno
            && $this->user()?->can('view', $alumno) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'seccion' => ['nullable', 'string', Rule::in(self::SECCIONES)],
        ];
    }

    public function seccion(): string
    {
        return (string) $this->validated('seccion');
    }
}
