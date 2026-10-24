<?php

namespace App\Http\Requests\Academico;

use App\Models\Grupo;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class GrupoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $grupo = $this->route('grupo');

        return $grupo instanceof Grupo
            ? ($this->user()?->can('update', $grupo) ?? false)
            : ($this->user()?->can('create', Grupo::class) ?? false);
    }

    private function referencia(string $tabla, string $campo, bool $porNivel = false): Exists
    {
        $grupo = $this->route('grupo');
        $regla = Rule::exists($tabla, 'id');

        if (! ($grupo instanceof Grupo && $grupo->getAttribute($campo) === $this->integer($campo))) {
            $regla->where('activo', true);
        }

        if ($porNivel) {
            $regla->where('nivel_id', $this->integer('nivel_id'));
        }

        return $regla;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $grupo = $this->route('grupo');

        return [
            'ciclo_lectivo' => [
                'required', 'integer', 'between:1900,9999',
                Rule::unique('grupos', 'ciclo_lectivo')->where(fn (Builder $query): Builder => $query
                    ->where('nivel_id', $this->integer('nivel_id'))
                    ->where('curso_id', $this->integer('curso_id'))
                    ->where('division_id', $this->integer('division_id'))
                    ->where('turno_id', $this->integer('turno_id')))
                    ->ignore($grupo instanceof Grupo ? $grupo : null),
            ],
            'nivel_id' => ['required', 'integer', $this->referencia('niveles', 'nivel_id')],
            'curso_id' => ['required', 'integer', $this->referencia('cursos', 'curso_id', true)],
            'division_id' => ['required', 'integer', $this->referencia('divisiones', 'division_id')],
            'turno_id' => ['required', 'integer', $this->referencia('turnos', 'turno_id')],
            'plan_estudio_id' => ['nullable', 'integer', $this->referencia('planes_estudio', 'plan_estudio_id', true)],
            'crear_otro' => ['sometimes', 'boolean'],
            'filtros' => ['sometimes', 'array:'.implode(',', array_keys(IndexGrupoRequest::reglasFiltros()))],
            ...IndexGrupoRequest::reglasFiltros('filtros.'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'ciclo_lectivo.unique' => 'Ya existe un grupo con ese ciclo, nivel, curso, división y turno. Si está inactivo, podés reactivarlo desde el listado.',
            'curso_id.exists' => 'Seleccioná un curso activo del nivel elegido; podés conservar el curso actual aunque esté inactivo.',
            'plan_estudio_id.exists' => 'Seleccioná un plan activo del nivel elegido; podés conservar el plan actual aunque esté inactivo.',
        ];
    }

    /** @return array<string, mixed> */
    public function datos(): array
    {
        return [...$this->safe()->only(['ciclo_lectivo', 'nivel_id', 'curso_id', 'division_id', 'turno_id']), 'plan_estudio_id' => $this->validated('plan_estudio_id')];
    }
}
