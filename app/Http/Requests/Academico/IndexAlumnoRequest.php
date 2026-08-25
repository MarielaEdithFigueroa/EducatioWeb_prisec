<?php

namespace App\Http\Requests\Academico;

use App\Models\Alumno;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexAlumnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Alumno::class) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'buscar' => ['nullable', 'string', 'max:100'],
            'provincia_id' => ['nullable', 'integer', 'exists:provincias,id'],
            'ciudad_id' => ['nullable', 'integer', 'exists:ciudades,id'],
            'solo_activos' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array{buscar: string, provincia_id: string, ciudad_id: string, solo_activos: bool}
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'buscar' => trim((string) ($validated['buscar'] ?? '')),
            'provincia_id' => (string) ($validated['provincia_id'] ?? ''),
            'ciudad_id' => (string) ($validated['ciudad_id'] ?? ''),
            'solo_activos' => $this->boolean('solo_activos'),
        ];
    }
}
