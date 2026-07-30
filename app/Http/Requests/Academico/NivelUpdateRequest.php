<?php

namespace App\Http\Requests\Academico;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NivelUpdateRequest extends FormRequest
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
            'codigo' => ['required', 'string', 'max:16', Rule::unique('niveles', 'codigo')->ignore($this->route('nivel'))],
            'descripcion' => ['required', 'string', 'max:128'],
        ];
    }
}
