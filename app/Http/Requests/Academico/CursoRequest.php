<?php

namespace App\Http\Requests\Academico;

use App\Models\Curso;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CursoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $curso = $this->route('curso');

        return $curso instanceof Curso
            ? ($this->user()?->can('update', $curso) ?? false)
            : ($this->user()?->can('create', Curso::class) ?? false);
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $curso = $this->route('curso');
        $mantieneNivelInactivo = $curso instanceof Curso
            && $curso->nivel_id === $this->integer('nivel_id');
        $nivelExistente = Rule::exists('niveles', 'id');

        if (! $mantieneNivelInactivo) {
            $nivelExistente->where('activo', true);
        }

        return [
            'descripcion' => [
                'required',
                'string',
                'max:100',
                Rule::unique('cursos', 'descripcion')
                    ->where(fn (Builder $query): Builder => $query->where('nivel_id', $this->integer('nivel_id')))
                    ->ignore($curso instanceof Curso ? $curso : null),
            ],
            'nivel_id' => ['required', 'integer', $nivelExistente],
            'orden' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $curso = $this->route('curso');

            if (
                $curso instanceof Curso
                && $curso->nivel_id !== $this->integer('nivel_id')
                && $curso->grupos()->exists()
            ) {
                $validator->errors()->add(
                    'nivel_id',
                    'No se puede cambiar el nivel porque el curso ya está usado en grupos.',
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
