<?php

namespace App\Http\Requests\Academico;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GrupoStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Autorización diferida: sin reglas todavía (ver core.md).
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'activo' => ['required', 'boolean'],
            'anio_lectivo_id' => ['required', 'integer', Rule::exists('anios_lectivos', 'id')],
            'curso_id' => ['required', 'integer', Rule::exists('cursos', 'id')],
            'division_id' => ['required', 'integer', Rule::exists('divisiones', 'id')],
            'turno_id' => [
                'required',
                'integer',
                Rule::exists('turnos', 'id'),
                Rule::unique('grupos', 'turno_id')->where(fn ($query) => $query
                    ->where('anio_lectivo_id', $this->input('anio_lectivo_id'))
                    ->where('curso_id', $this->input('curso_id'))
                    ->where('division_id', $this->input('division_id'))),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'turno_id.unique' => 'Ya existe un grupo con esa combinación de año lectivo, curso, división y turno.',
        ];
    }
}
