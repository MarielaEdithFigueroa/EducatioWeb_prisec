<?php

namespace App\Http\Requests\Academico;

use App\Models\Grupo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexGrupoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Grupo::class) === true;
    }

    /** @return array<string, list<mixed>> */
    public static function reglasFiltros(string $prefijo = ''): array
    {
        return [
            $prefijo.'ciclo_lectivo' => ['nullable', 'integer', 'between:1900,9999'],
            $prefijo.'nivel_id' => ['nullable', 'integer', 'exists:niveles,id'],
            $prefijo.'curso_id' => ['nullable', 'integer', 'exists:cursos,id'],
            $prefijo.'division_id' => ['nullable', 'integer', 'exists:divisiones,id'],
            $prefijo.'turno_id' => ['nullable', 'integer', 'exists:turnos,id'],
            $prefijo.'plan_estudio_id' => ['nullable', 'integer', 'exists:planes_estudio,id'],
            $prefijo.'estado' => ['nullable', Rule::in(['activos', 'inactivos'])],
        ];
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return $this->routeIs('academico.grupos.index')
            ? self::reglasFiltros()
            : ['filtros' => ['sometimes', 'array:'.implode(',', array_keys(self::reglasFiltros()))], ...self::reglasFiltros('filtros.')];
    }

    /** @return array<string, string> */
    public function filters(): array
    {
        $datos = $this->routeIs('academico.grupos.index') ? $this->validated() : $this->validated('filtros', []);

        return array_map(fn (mixed $valor): string => (string) ($valor ?? ''), array_replace(array_fill_keys(array_keys(self::reglasFiltros()), ''), $datos));
    }
}
