<?php

namespace App\Http\Requests\Sistema;

use App\Models\Ciudad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LocalidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $localidad = $this->route('localidad');

        return $localidad instanceof Ciudad
            ? ($this->user()?->can('update', $localidad) ?? false)
            : ($this->user()?->can('create', Ciudad::class) ?? false);
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $localidad = $this->route('localidad');
        $provinciaActualId = $localidad instanceof Ciudad
            ? $localidad->provincia_id
            : null;

        return [
            'provincia_id' => [
                'required',
                'integer',
                Rule::exists('provincias', 'id')->where(function ($consulta) use ($provinciaActualId): void {
                    $consulta->where('activo', true);

                    if ($provinciaActualId !== null) {
                        $consulta->orWhere('id', $provinciaActualId);
                    }
                }),
            ],
            'nombre' => [
                'required',
                'string',
                'max:120',
                Rule::unique('ciudades', 'nombre')
                    ->where(fn ($consulta) => $consulta->where('provincia_id', $this->integer('provincia_id')))
                    ->ignore($localidad instanceof Ciudad ? $localidad : null),
            ],
            'codigo_postal' => ['nullable', 'string', 'max:10'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $codigoPostal = Str::upper(Str::squish((string) $this->input('codigo_postal')));

        $this->merge([
            'nombre' => Str::squish((string) $this->input('nombre')),
            'codigo_postal' => $codigoPostal !== '' ? $codigoPostal : null,
        ]);
    }
}
