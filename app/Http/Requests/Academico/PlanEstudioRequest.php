<?php

namespace App\Http\Requests\Academico;

use App\Models\PlanEstudio;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PlanEstudioRequest extends FormRequest
{
    public function authorize(): bool
    {
        $planEstudio = $this->route('planEstudio');

        return $planEstudio instanceof PlanEstudio
            ? ($this->user()?->can('update', $planEstudio) ?? false)
            : ($this->user()?->can('create', PlanEstudio::class) ?? false);
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $planEstudio = $this->route('planEstudio');
        $mantieneNivelInactivo = $planEstudio instanceof PlanEstudio
            && $planEstudio->nivel_id === $this->integer('nivel_id');
        $nivelExistente = Rule::exists('niveles', 'id');

        if (! $mantieneNivelInactivo) {
            $nivelExistente->where('activo', true);
        }

        return [
            'descripcion' => [
                'required',
                'string',
                'max:150',
                Rule::unique('planes_estudio', 'descripcion')
                    ->where(fn (Builder $query): Builder => $query->where('nivel_id', $this->integer('nivel_id')))
                    ->ignore($planEstudio instanceof PlanEstudio ? $planEstudio : null),
            ],
            'nivel_id' => ['required', 'integer', $nivelExistente],
            'orden' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $planEstudio = $this->route('planEstudio');

            if (
                $planEstudio instanceof PlanEstudio
                && $planEstudio->nivel_id !== $this->integer('nivel_id')
                && $planEstudio->grupos()->exists()
            ) {
                $validator->errors()->add(
                    'nivel_id',
                    'No se puede cambiar el nivel porque el plan ya está usado en grupos.',
                );
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'descripcion' => Str::squish((string) $this->input('descripcion')),
        ]);
    }
}
