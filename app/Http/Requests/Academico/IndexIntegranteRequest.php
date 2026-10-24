<?php

namespace App\Http\Requests\Academico;

use App\Models\Integrante;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexIntegranteRequest extends FormRequest
{
    public const SECCIONES = ['personal', 'documentacion', 'adjuntos', 'otros'];

    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Integrante::class) === true;
    }

    /** @return array<string, list<mixed>> */
    public static function reglasFiltros(string $prefijo = ''): array
    {
        return [
            $prefijo.'buscar' => ['nullable', 'string', 'max:150'],
            $prefijo.'estado' => ['nullable', Rule::in(['activos', 'inactivos'])],
            $prefijo.'funcion_id' => ['nullable', 'integer', 'exists:funciones_equipo,id'],
        ];
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return $this->routeIs('academico.integrantes.index')
            ? self::reglasFiltros()
            : ['seccion' => ['nullable', Rule::in(self::SECCIONES)], 'filtros' => ['sometimes', 'array:buscar,estado,funcion_id'], ...self::reglasFiltros('filtros.')];
    }

    public function seccion(): string
    {
        return $this->validated('seccion') ?? 'personal';
    }

    /** @return array<string, string> */
    public function filters(): array
    {
        $datos = $this->routeIs('academico.integrantes.index') ? $this->validated() : $this->validated('filtros', []);

        return array_map(fn (mixed $valor): string => (string) ($valor ?? ''), array_replace(
            ['buscar' => '', 'estado' => 'activos', 'funcion_id' => ''],
            $datos,
        ));
    }
}
