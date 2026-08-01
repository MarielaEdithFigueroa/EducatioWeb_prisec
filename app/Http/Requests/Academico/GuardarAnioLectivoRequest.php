<?php

namespace App\Http\Requests\Academico;

use App\EstadoAnioLectivo;
use App\Models\AnioLectivo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GuardarAnioLectivoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $anioLectivo = $this->route('anioLectivo');

        return $anioLectivo instanceof AnioLectivo
            ? $this->user()?->can('update', $anioLectivo) === true
            : $this->user()?->can('create', AnioLectivo::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        $anioLectivo = $this->route('anioLectivo');

        return [
            'anio' => [
                'required',
                'integer',
                'between:2000,2200',
                Rule::unique('anio_lectivos', 'anio')
                    ->ignore($anioLectivo instanceof AnioLectivo ? $anioLectivo : null),
            ],
        ];
    }

    /** @return array<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $anioLectivo = $this->route('anioLectivo');

            if ($anioLectivo instanceof AnioLectivo
                && $anioLectivo->anio !== $this->integer('anio')
                && ($anioLectivo->estado !== EstadoAnioLectivo::Preparacion || $anioLectivo->grupos()->exists())) {
                $validator->errors()->add(
                    'anio',
                    'El número sólo puede cambiarse mientras el año está en preparación y no tiene grupos.',
                );
            }
        }];
    }
}
