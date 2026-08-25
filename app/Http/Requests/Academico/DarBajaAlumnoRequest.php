<?php

namespace App\Http\Requests\Academico;

use App\Models\Alumno;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DarBajaAlumnoRequest extends FormRequest
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
        return [
            'fecha_baja' => ['required', 'date', 'before_or_equal:today'],
            'motivo_baja_id' => [
                'required',
                'integer',
                Rule::exists('motivos_baja', 'id')
                    ->where(fn (Builder $consulta) => $consulta->where('activo', true)),
            ],
        ];
    }
}
