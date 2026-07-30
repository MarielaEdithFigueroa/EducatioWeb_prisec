<?php

namespace App\Http\Requests\Academico;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CursoStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Autorización diferida: sin reglas todavía (ver core.md).
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'activo' => ['required', 'boolean'],
            'nivel_id' => ['required', 'integer', Rule::exists('niveles', 'id')],
            'codigo' => ['required', 'string', 'max:16'],
            'descripcion' => ['required', 'string', 'max:128'],
            'orden' => ['required', 'integer', 'min:0'],
        ];
    }
}
