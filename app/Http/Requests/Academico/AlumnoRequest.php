<?php

namespace App\Http\Requests\Academico;

use App\Models\Alumno;
use App\Rules\Cuit;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class AlumnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $alumno = $this->route('alumno');

        return $alumno instanceof Alumno
            ? ($this->user()?->can('update', $alumno) ?? false)
            : ($this->user()?->can('create', Alumno::class) ?? false);
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $alumno = $this->route('alumno');

        return [
            'legajo' => [
                'required',
                'string',
                'max:30',
                Rule::unique('alumnos', 'legajo')->ignore($alumno instanceof Alumno ? $alumno : null),
            ],
            'apellido' => ['required', 'string', 'max:100'],
            'nombre' => ['required', 'string', 'max:100'],
            'nombre_elegido' => ['nullable', 'string', 'max:100'],
            'tipo_documento_id' => [
                'nullable',
                'required_with:numero_documento',
                'integer',
                Rule::exists('tipos_documento', 'id'),
            ],
            'numero_documento' => [
                'nullable',
                'required_with:tipo_documento_id',
                'string',
                'max:20',
                Rule::unique('alumnos', 'numero_documento')
                    ->where(fn (Builder $consulta) => $consulta->where(
                        'tipo_documento_id',
                        $this->integer('tipo_documento_id'),
                    ))
                    ->ignore($alumno instanceof Alumno ? $alumno : null),
            ],
            'cuilt' => [
                'nullable',
                new Cuit,
                Rule::unique('alumnos', 'cuilt')->ignore($alumno instanceof Alumno ? $alumno : null),
            ],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'ciudad_nacimiento_id' => [
                'nullable',
                'integer',
                $this->existeActivo('ciudades', $alumno instanceof Alumno ? $alumno->ciudad_nacimiento_id : null),
            ],
            'nacionalidad_id' => [
                'nullable',
                'integer',
                $this->existeActivo('nacionalidades', $alumno instanceof Alumno ? $alumno->nacionalidad_id : null),
            ],
            'grupo_sanguineo_id' => ['nullable', 'integer', Rule::exists('grupos_sanguineos', 'id')],
            'sexo_registral' => ['nullable', 'string', Rule::in(['F', 'M', 'X'])],
            'genero' => [
                'nullable',
                'string',
                Rule::in([
                    'Mujer',
                    'Varón',
                    'No binario',
                    'Otra identidad',
                    'Prefiere no informar',
                ]),
            ],
            'genero_autodescripcion' => [
                'nullable',
                'required_if:genero,Otra identidad',
                'string',
                'max:100',
            ],
            'email' => ['nullable', 'email', 'max:254'],
            'domicilio' => ['nullable', 'string', 'max:200'],
            'ciudad_id' => [
                'nullable',
                'integer',
                $this->existeActivo('ciudades', $alumno instanceof Alumno ? $alumno->ciudad_id : null),
            ],
            'cpa' => ['nullable', 'string', 'max:10'],
            'fecha_ingreso' => ['nullable', 'date'],
            'fecha_inicio_cursado' => ['nullable', 'date'],
            'libro' => ['nullable', 'string', 'max:30'],
            'folio' => ['nullable', 'string', 'max:30'],
            'autoriza_uso_imagen' => ['nullable', 'boolean'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $genero = $this->normalizarTexto($this->input('genero'));
        $cuilt = preg_replace('/\D+/', '', (string) $this->input('cuilt'));

        $this->merge([
            'legajo' => trim((string) $this->input('legajo')),
            'apellido' => Str::squish((string) $this->input('apellido')),
            'nombre' => Str::squish((string) $this->input('nombre')),
            'nombre_elegido' => $this->normalizarTexto($this->input('nombre_elegido')),
            'numero_documento' => $this->normalizarMayusculas($this->input('numero_documento')),
            'cuilt' => $cuilt !== '' ? $cuilt : null,
            'sexo_registral' => $this->normalizarMayusculas($this->input('sexo_registral')),
            'genero' => $genero,
            'genero_autodescripcion' => $genero === 'Otra identidad'
                ? $this->normalizarTexto($this->input('genero_autodescripcion'))
                : null,
            'email' => $this->normalizarMinusculas($this->input('email')),
            'domicilio' => $this->normalizarTexto($this->input('domicilio')),
            'cpa' => $this->normalizarMayusculas($this->input('cpa')),
            'libro' => $this->normalizarTexto($this->input('libro')),
            'folio' => $this->normalizarTexto($this->input('folio')),
            'observaciones' => $this->normalizarMultilinea($this->input('observaciones')),
        ]);
    }

    private function existeActivo(string $tabla, ?int $idActual): Exists
    {
        return Rule::exists($tabla, 'id')->where(function (Builder $consulta) use ($idActual): void {
            $consulta->where('activo', true);

            if ($idActual !== null) {
                $consulta->orWhere('id', $idActual);
            }
        });
    }

    private function normalizarTexto(mixed $valor): ?string
    {
        $texto = Str::squish((string) $valor);

        return $texto !== '' ? $texto : null;
    }

    private function normalizarMayusculas(mixed $valor): ?string
    {
        $texto = $this->normalizarTexto($valor);

        return $texto !== null ? Str::upper($texto) : null;
    }

    private function normalizarMinusculas(mixed $valor): ?string
    {
        $texto = $this->normalizarTexto($valor);

        return $texto !== null ? Str::lower($texto) : null;
    }

    private function normalizarMultilinea(mixed $valor): ?string
    {
        $texto = trim((string) $valor);

        return $texto !== '' ? $texto : null;
    }
}
