<?php

namespace App\Http\Requests\Academico;

use App\Models\Alumno;
use App\Models\Grupo;
use Illuminate\Foundation\Http\FormRequest;

class PanelGruposRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Grupo::class) === true
            && $this->user()?->can('viewAny', Alumno::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'ciclo_lectivo' => ['nullable', 'integer', 'between:1900,9999'],
        ];
    }
}
