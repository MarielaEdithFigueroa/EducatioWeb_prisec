<?php

namespace App\Http\Requests\Academico;

use App\Models\Responsable;
use Illuminate\Foundation\Http\FormRequest;

class IndexResponsableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Responsable::class) === true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'buscar' => ['nullable', 'string', 'max:100'],
            'provincia_id' => ['nullable', 'integer', 'exists:provincias,id'],
            'ciudad_id' => ['nullable', 'integer', 'exists:ciudades,id'],
            'solo_activos' => ['nullable', 'boolean'],
        ];
    }

    /** @return array{buscar: string, provincia_id: string, ciudad_id: string, solo_activos: bool} */
    public function filters(): array
    {
        $datos = $this->validated();

        return [
            'buscar' => trim((string) ($datos['buscar'] ?? '')),
            'provincia_id' => (string) ($datos['provincia_id'] ?? ''),
            'ciudad_id' => (string) ($datos['ciudad_id'] ?? ''),
            'solo_activos' => $this->boolean('solo_activos'),
        ];
    }
}
