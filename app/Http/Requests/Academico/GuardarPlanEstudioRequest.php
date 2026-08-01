<?php

namespace App\Http\Requests\Academico;

use App\Models\PlanEstudio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GuardarPlanEstudioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $planEstudio = $this->route('planEstudio');

        return $planEstudio instanceof PlanEstudio
            ? $this->user()?->can('update', $planEstudio) === true
            : $this->user()?->can('create', PlanEstudio::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        $planEstudio = $this->route('planEstudio');

        return [
            'nivel_id' => [
                'required',
                'integer',
                Rule::exists('niveles', 'id')->where('activo', true),
            ],
            'codigo' => [
                'required',
                'string',
                'max:32',
                Rule::unique('planes_estudio', 'codigo')
                    ->where('nivel_id', $this->integer('nivel_id'))
                    ->ignore($planEstudio instanceof PlanEstudio ? $planEstudio : null),
            ],
            'descripcion' => ['required', 'string', 'max:128'],
        ];
    }

    /** @return array<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $planEstudio = $this->route('planEstudio');

            if ($planEstudio instanceof PlanEstudio
                && $planEstudio->nivel_id !== $this->integer('nivel_id')
                && $planEstudio->grupos()->exists()) {
                $validator->errors()->add(
                    'nivel_id',
                    'El nivel no puede cambiarse porque el plan ya está utilizado por grupos.',
                );
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'codigo' => $this->string('codigo')->trim()->upper()->toString(),
            'descripcion' => $this->string('descripcion')->trim()->toString(),
        ]);
    }
}
