<?php

namespace App\Http\Requests\Academico;

use App\Models\Responsable;
use App\Rules\Cuit;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class ResponsableRequest extends FormRequest
{
    private ?string $telefonoOriginal = null;

    private ?string $telefonoLaboralOriginal = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $responsable = $this->route('responsable');

        return $responsable instanceof Responsable
            && ($this->user()?->can('update', $responsable) ?? false);
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $responsable = $this->route('responsable');
        $actual = $responsable instanceof Responsable ? $responsable : null;

        return [
            'responsable.apellido' => ['required', 'string', 'max:100'],
            'responsable.nombre' => ['required', 'string', 'max:100'],
            'responsable.nombre_elegido' => ['nullable', 'string', 'max:100'],
            'responsable.tipo_documento_id' => [
                'nullable',
                'required_with:responsable.numero_documento',
                'integer',
                Rule::exists('tipos_documento', 'id'),
            ],
            'responsable.numero_documento' => [
                'nullable',
                'required_with:responsable.tipo_documento_id',
                'string',
                'max:20',
                Rule::unique('responsables', 'numero_documento')->ignore($actual)->where(
                    fn (Builder $consulta) => $consulta->where(
                        'tipo_documento_id',
                        $this->input('responsable.tipo_documento_id'),
                    ),
                ),
            ],
            'responsable.cuilt' => ['nullable', new Cuit, Rule::unique('responsables', 'cuilt')->ignore($actual)],
            'responsable.fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'responsable.sexo_registral' => ['nullable', 'string', Rule::in(['F', 'M', 'X'])],
            'responsable.genero' => ['nullable', 'string', Rule::in([
                'Mujer', 'Varón', 'No binario', 'Otra identidad', 'Prefiere no informar',
            ])],
            'responsable.genero_autodescripcion' => [
                'nullable', 'required_if:responsable.genero,Otra identidad', 'string', 'max:100',
            ],
            'responsable.email' => ['nullable', 'email', 'max:254'],
            'responsable.telefono' => ['nullable', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
            'responsable.domicilio' => ['nullable', 'string', 'max:200'],
            'responsable.ciudad_id' => ['nullable', 'integer', $this->existeActivo('ciudades', $actual?->ciudad_id)],
            'responsable.cpa' => ['nullable', 'string', 'max:10'],
            'responsable.profesion' => ['nullable', 'string', 'max:100'],
            'responsable.domicilio_laboral' => ['nullable', 'string', 'max:200'],
            'responsable.ciudad_laboral_id' => ['nullable', 'integer', $this->existeActivo('ciudades', $actual?->ciudad_laboral_id)],
            'responsable.cpa_laboral' => ['nullable', 'string', 'max:10'],
            'responsable.telefono_laboral' => ['nullable', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
            'responsable.observaciones' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $responsable = (array) $this->input('responsable', []);
        $genero = $this->texto($responsable['genero'] ?? null);
        $this->telefonoOriginal = $this->texto($responsable['telefono'] ?? null);
        $this->telefonoLaboralOriginal = $this->texto($responsable['telefono_laboral'] ?? null);

        $this->merge([
            'responsable' => [
                ...$responsable,
                'apellido' => Str::squish((string) ($responsable['apellido'] ?? '')),
                'nombre' => Str::squish((string) ($responsable['nombre'] ?? '')),
                'nombre_elegido' => $this->texto($responsable['nombre_elegido'] ?? null),
                'numero_documento' => $this->mayusculas($responsable['numero_documento'] ?? null),
                'cuilt' => $this->soloDigitos($responsable['cuilt'] ?? null),
                'sexo_registral' => $this->mayusculas($responsable['sexo_registral'] ?? null),
                'genero' => $genero,
                'genero_autodescripcion' => $genero === 'Otra identidad'
                    ? $this->texto($responsable['genero_autodescripcion'] ?? null)
                    : null,
                'email' => $this->minusculas($responsable['email'] ?? null),
                'telefono' => $this->telefono($responsable['telefono'] ?? null),
                'domicilio' => $this->texto($responsable['domicilio'] ?? null),
                'cpa' => $this->mayusculas($responsable['cpa'] ?? null),
                'profesion' => $this->texto($responsable['profesion'] ?? null),
                'domicilio_laboral' => $this->texto($responsable['domicilio_laboral'] ?? null),
                'cpa_laboral' => $this->mayusculas($responsable['cpa_laboral'] ?? null),
                'telefono_laboral' => $this->telefono($responsable['telefono_laboral'] ?? null),
                'observaciones' => $this->multilinea($responsable['observaciones'] ?? null),
            ],
        ]);
    }

    /** @return array<string, mixed> */
    public function datosResponsable(): array
    {
        $datos = $this->safe()->array('responsable');
        $telefono = $datos['telefono'] ?? null;
        $telefonoLaboral = $datos['telefono_laboral'] ?? null;
        unset($datos['telefono'], $datos['telefono_laboral']);

        return [
            ...$datos,
            'telefono_e164' => $telefono,
            'telefono_original' => $this->telefonoOriginal,
            'telefono_laboral_e164' => $telefonoLaboral,
            'telefono_laboral_original' => $this->telefonoLaboralOriginal,
        ];
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

    private function texto(mixed $valor): ?string
    {
        $texto = Str::squish((string) $valor);

        return $texto !== '' ? $texto : null;
    }

    private function mayusculas(mixed $valor): ?string
    {
        $texto = $this->texto($valor);

        return $texto !== null ? Str::upper($texto) : null;
    }

    private function minusculas(mixed $valor): ?string
    {
        $texto = $this->texto($valor);

        return $texto !== null ? Str::lower($texto) : null;
    }

    private function soloDigitos(mixed $valor): ?string
    {
        $texto = preg_replace('/\D+/', '', (string) $valor) ?? '';

        return $texto !== '' ? $texto : null;
    }

    private function telefono(mixed $valor): ?string
    {
        $texto = preg_replace('/[\s().-]+/', '', (string) $valor) ?? '';

        return $texto !== '' ? $texto : null;
    }

    private function multilinea(mixed $valor): ?string
    {
        $texto = trim((string) $valor);

        return $texto !== '' ? $texto : null;
    }
}
