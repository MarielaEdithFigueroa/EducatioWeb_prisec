<?php

namespace App\Http\Requests\Academico;

use App\Models\Grupo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListarGruposRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Grupo::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'anio_lectivo_id' => ['nullable', 'integer', Rule::exists('anio_lectivos', 'id')],
            'nivel_id' => ['nullable', 'integer', Rule::exists('niveles', 'id')],
            'estado' => ['nullable', Rule::in(['activos', 'inactivos', 'todos'])],
        ];
    }
}
