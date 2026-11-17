<?php

namespace App\Http\Requests\Academico;

use App\Models\Grupo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AsignarAlumnosGrupoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $grupo = $this->route('grupo');

        return $grupo instanceof Grupo && $this->user()?->can('update', $grupo) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'alumnos' => ['required', 'array', 'list', 'min:1', 'max:200'],
            'alumnos.*' => ['required', 'array:alumno_id,version_historial'],
            'alumnos.*.alumno_id' => ['required', 'integer', 'distinct', Rule::exists('alumnos', 'id')->where('activo', true)],
            'alumnos.*.version_historial' => ['required', 'string', 'max:50'],
            'motivo' => ['required', 'string', 'max:500'],
        ];
    }

    /** @return array<callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->route('grupo')->activo) {
                $validator->errors()->add('alumnos', 'El grupo de destino está inactivo.');
            }
        }];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['alumnos.*.alumno_id' => 'alumno seleccionado', 'alumnos.*.version_historial' => 'versión de la asignación'];
    }
}
