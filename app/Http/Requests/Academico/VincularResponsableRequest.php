<?php

namespace App\Http\Requests\Academico;

use App\Models\Alumno;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VincularResponsableRequest extends FormRequest
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

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'responsable_id' => ['required', 'integer', Rule::exists('responsables', 'id')],
            ...self::reglasVinculo(),
        ];
    }

    /** @return array<string, list<mixed>> */
    public static function reglasVinculo(): array
    {
        return [
            'vinculo.vinculo_id' => ['required', 'integer', Rule::exists('tipos_vinculo', 'id')],
            'vinculo.fecha_desde' => ['nullable', 'date'],
            'vinculo.fecha_hasta' => ['nullable', 'date', 'after_or_equal:vinculo.fecha_desde'],
            'vinculo.es_responsable_legal' => ['required', 'boolean'],
            'vinculo.es_responsable_pedagogico' => ['required', 'boolean'],
            'vinculo.es_responsable_economico' => ['required', 'boolean'],
            'vinculo.recibe_comunicaciones' => ['required', 'boolean'],
            'vinculo.recibe_informes_academicos' => ['required', 'boolean'],
            'vinculo.recibe_facturacion' => ['required', 'boolean'],
            'vinculo.es_contacto_principal' => ['required', 'boolean'],
            'vinculo.es_contacto_emergencia' => ['required', 'boolean'],
            'vinculo.convive_con_alumno' => ['required', 'boolean'],
            'vinculo.autorizado_retirar' => ['required', 'boolean'],
            'vinculo.habilitado_portal' => ['required', 'boolean'],
            'vinculo.observaciones' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $vinculo = (array) $this->input('vinculo', []);
        $observaciones = trim((string) ($vinculo['observaciones'] ?? ''));

        $this->merge([
            'vinculo' => [
                ...$vinculo,
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
