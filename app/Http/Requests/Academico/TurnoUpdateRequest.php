<?php

namespace App\Http\Requests\Academico;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TurnoUpdateRequest extends FormRequest
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
            'codigo' => ['required', 'string', 'max:16', Rule::unique('turnos', 'codigo')->ignore($this->route('turno'))],
            'descripcion' => ['required', 'string', 'max:128'],
        ];
    }
}
