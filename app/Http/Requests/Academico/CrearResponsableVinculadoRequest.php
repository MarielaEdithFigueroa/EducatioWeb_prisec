<?php

namespace App\Http\Requests\Academico;

use App\Models\Alumno;
use App\Models\Responsable;

class CrearResponsableVinculadoRequest extends ResponsableRequest
{
    public function authorize(): bool
    {
        $alumno = $this->route('alumno');

        return $alumno instanceof Alumno
            && ($this->user()?->can('update', $alumno) ?? false)
            && ($this->user()?->can('create', Responsable::class) ?? false);
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            ...VincularResponsableRequest::reglasVinculo(),
        ];
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $observaciones = trim((string) $this->input('vinculo.observaciones'));
        $this->merge([
            'vinculo' => [
                ...(array) $this->input('vinculo', []),
                'observaciones' => $observaciones !== '' ? $observaciones : null,
            ],
        ]);
    }

    /** @return array<string, mixed> */
    public function datosVinculo(): array
    {
        return $this->safe()->array('vinculo');
    }
}
