<?php

namespace App\Http\Requests\Academico;

use App\Models\Grupo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class DesactivarGrupoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('grupo')) === true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [];
    }

    /** @return array<callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->tieneAlumnos($this->route('grupo'))) {
                $validator->errors()->add('grupo', $this->mensaje());
            }
        }];
    }

    public function validarGrupo(Grupo $grupo): void
    {
        if ($this->tieneAlumnos($grupo)) {
            throw ValidationException::withMessages(['grupo' => $this->mensaje()]);
        }
    }

    private function tieneAlumnos(Grupo $grupo): bool
    {
        return $grupo->alumnos()->exists() || $grupo->historialAlumnos()->whereNull('fecha_hasta')->exists();
    }

    private function mensaje(): string
    {
        return 'El grupo tiene alumnos asignados. Trasladalos o cerrá sus asignaciones antes de desactivarlo.';
    }
}
