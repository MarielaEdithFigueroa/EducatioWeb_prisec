<?php

namespace App\Http\Requests\Academico;

use App\Models\Alumno;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CambiarGrupoAlumnoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $alumno = $this->route('alumno');

        return $alumno instanceof Alumno && $this->user()?->can('update', $alumno) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'grupo_id' => ['present', 'nullable', 'integer', Rule::exists('grupos', 'id')->where('activo', true)],
            'version_historial' => ['required', 'string', 'max:50'],
            'motivo' => ['required', 'string', 'max:500'],
        ];
    }

    /** @return array<callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->route('alumno')->activo) {
                $validator->errors()->add('grupo_id', 'Reactivá al alumno antes de asignarle un grupo.');
            }
        }];
    }
}
