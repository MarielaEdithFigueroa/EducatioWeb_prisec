<?php

namespace App\Http\Requests\Academico;

use App\EstadoAnioLectivo;
use App\Models\AnioLectivo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProyectarGruposRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $anioLectivo = $this->route('anioLectivo');

        return $anioLectivo instanceof AnioLectivo
            && $this->user()?->can('projectGroups', $anioLectivo) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'destino_id' => [
                'required',
                'integer',
                Rule::exists('anio_lectivos', 'id')->where('activo', true),
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

            $origen = $this->route('anioLectivo');
            $destino = AnioLectivo::query()->find($this->integer('destino_id'));

            if (! $origen instanceof AnioLectivo || ! $destino instanceof AnioLectivo) {
                return;
            }

            if ($destino->id === $origen->id) {
                $validator->errors()->add('destino_id', 'El año de destino debe ser distinto del año de origen.');
            }

            if (! $origen->activo) {
                $validator->errors()->add('anio_lectivo_id', 'El año de origen debe estar activo.');
            }

            if ($destino->estado !== EstadoAnioLectivo::Preparacion) {
                $validator->errors()->add('destino_id', 'El año de destino debe estar en preparación.');
            }

            if ($destino->anio <= $origen->anio) {
                $validator->errors()->add('destino_id', 'El año de destino debe ser posterior al de origen.');
            }
        }];
    }
}
