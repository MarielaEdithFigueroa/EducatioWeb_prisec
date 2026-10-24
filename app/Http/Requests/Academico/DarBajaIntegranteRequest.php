<?php

namespace App\Http\Requests\Academico;

use App\Models\Integrante;
use Illuminate\Foundation\Http\FormRequest;

class DarBajaIntegranteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $integrante = $this->route('integrante');

        return $integrante instanceof Integrante && $this->user()?->can('update', $integrante) === true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $ingreso = $this->route('integrante')?->fecha_ingreso?->format('Y-m-d');

        return [
            'fecha_baja' => ['required', 'date_format:Y-m-d', 'before_or_equal:today', ...($ingreso ? ['after_or_equal:'.$ingreso] : [])],
            'motivo_baja' => ['required', 'string', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('motivo_baja'))) {
            $this->merge(['motivo_baja' => trim($this->input('motivo_baja'))]);
        }
    }
}
