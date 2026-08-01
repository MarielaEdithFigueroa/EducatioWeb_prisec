<?php

namespace App\Http\Requests\Academico;

use App\Models\AnioLectivo;
use Illuminate\Foundation\Http\FormRequest;

class MarcarAnioLectivoVigenteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $anioLectivo = $this->route('anioLectivo');

        return $anioLectivo instanceof AnioLectivo
            && $this->user()?->can('markAsCurrent', $anioLectivo) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
        ];
    }
}
