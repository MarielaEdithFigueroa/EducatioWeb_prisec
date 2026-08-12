<?php

namespace App\Http\Requests\Academico;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class CambiarEstadoCatalogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $modelo = collect($this->route()?->parameters() ?? [])
            ->first(fn (mixed $parametro): bool => $parametro instanceof Model);

        return $modelo instanceof Model
            && ($this->user()?->can('update', $modelo) ?? false);
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [];
    }
}
