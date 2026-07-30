<?php

namespace App\Http\Requests\Academico;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnioLectivoStoreRequest extends FormRequest
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
            'anio' => ['required', 'integer', 'between:1990,2100', Rule::unique('anios_lectivos', 'anio')],
            'vigente' => ['required', 'boolean'],
        ];
    }
}
