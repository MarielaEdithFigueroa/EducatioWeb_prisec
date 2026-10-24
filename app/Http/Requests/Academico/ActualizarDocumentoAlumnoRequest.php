<?php

namespace App\Http\Requests\Academico;

use App\Models\Adjunto;
use App\Models\Alumno;
use Illuminate\Foundation\Http\FormRequest;

class ActualizarDocumentoAlumnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $alumno = $this->route('alumno');
        $adjunto = $this->route('adjunto');

        return $alumno instanceof Alumno
            && $adjunto instanceof Adjunto
            && $this->user()?->can('update', $alumno) === true
            && $this->user()?->can('update', $adjunto) === true;
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        return [
            'observaciones' => ['present', 'nullable', 'string', 'max:2000'],
        ];
    }
}
