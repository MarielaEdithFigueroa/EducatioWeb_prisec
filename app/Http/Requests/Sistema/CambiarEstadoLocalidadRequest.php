<?php

namespace App\Http\Requests\Sistema;

use App\Models\Ciudad;
use Illuminate\Foundation\Http\FormRequest;

class CambiarEstadoLocalidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $localidad = $this->route('localidad');

        return $localidad instanceof Ciudad
            && ($this->user()?->can('update', $localidad) ?? false);
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [];
    }
}
