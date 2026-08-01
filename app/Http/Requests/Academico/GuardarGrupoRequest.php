<?php

namespace App\Http\Requests\Academico;

use App\EstadoAnioLectivo;
use App\Models\AnioLectivo;
use App\Models\Curso;
use App\Models\Division;
use App\Models\Grupo;
use App\Models\PlanEstudio;
use App\Models\Turno;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Validator;

class GuardarGrupoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $grupo = $this->route('grupo');

        return $grupo instanceof Grupo
            ? $this->user()?->can('update', $grupo) === true
            : $this->user()?->can('create', Grupo::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        $grupo = $this->grupo();
        $combinacionUnica = Rule::unique('grupos', 'turno_id')
            ->where('anio_lectivo_id', $this->integer('anio_lectivo_id'))
            ->where('plan_estudio_id', $this->integer('plan_estudio_id'))
            ->where('curso_id', $this->integer('curso_id'))
            ->where('division_id', $this->integer('division_id'))
            ->ignore($grupo);

        if ($grupo === null) {
            $combinacionUnica->where('activo', true);
        }

        return [
            'anio_lectivo_id' => ['required', 'integer', $this->referenciaActivaOActual('anio_lectivos', 'anio_lectivo_id', $grupo)],
            'plan_estudio_id' => ['required', 'integer', $this->referenciaActivaOActual('planes_estudio', 'plan_estudio_id', $grupo)],
            'curso_id' => ['required', 'integer', $this->referenciaActivaOActual('cursos', 'curso_id', $grupo)],
            'division_id' => ['required', 'integer', $this->referenciaActivaOActual('divisiones', 'division_id', $grupo)],
            'turno_id' => [
                'required',
                'integer',
                $this->referenciaActivaOActual('turnos', 'turno_id', $grupo),
                $combinacionUnica,
            ],
        ];
    }

    /** @return array<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $niveles = collect([
                PlanEstudio::query()->find($this->integer('plan_estudio_id'))?->nivel_id,
                Curso::query()->find($this->integer('curso_id'))?->nivel_id,
                Division::query()->find($this->integer('division_id'))?->nivel_id,
                Turno::query()->find($this->integer('turno_id'))?->nivel_id,
            ])->filter()->unique();

            if ($niveles->count() !== 1) {
                $validator->errors()->add('nivel_id', 'El plan, curso, división y turno deben pertenecer al mismo nivel.');
            }

            $anioLectivo = AnioLectivo::query()->find($this->integer('anio_lectivo_id'));
            $conservaAnioHistorico = $this->grupo()?->anio_lectivo_id === $anioLectivo?->id;

            if ($anioLectivo?->estado === EstadoAnioLectivo::Cerrado && ! $conservaAnioHistorico) {
                $validator->errors()->add('anio_lectivo_id', 'No se pueden crear grupos en un año lectivo cerrado.');
            }
        }];
    }

    private function grupo(): ?Grupo
    {
        $grupo = $this->route('grupo');

        return $grupo instanceof Grupo ? $grupo : null;
    }

    private function referenciaActivaOActual(string $tabla, string $campo, ?Grupo $grupo): Exists
    {
        $idActual = $grupo?->getAttribute($campo);

        return Rule::exists($tabla, 'id')->where(function ($query) use ($idActual): void {
            $query->where('activo', true);

            if ($idActual !== null) {
                $query->orWhere('id', $idActual);
            }
        });
    }
}
