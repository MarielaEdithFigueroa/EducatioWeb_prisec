<?php

namespace App\Http\Requests\Academico;

use App\Models\Alumno;
use Illuminate\Foundation\Http\FormRequest;

class CambiarEstadoAlumnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $alumno = $this->route('alumno');

        return $alumno instanceof Alumno
            && ($this->user()?->can('update', $alumno) ?? false);
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [];
    }
}
