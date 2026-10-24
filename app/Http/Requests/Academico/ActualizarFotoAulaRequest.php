<?php

namespace App\Http\Requests\Academico;

use App\Models\Adjunto;
use App\Models\Aula;
use Illuminate\Foundation\Http\FormRequest;

class ActualizarFotoAulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $aula = $this->route('aula');
        $adjunto = $this->route('adjunto');

        return $aula instanceof Aula
            && $adjunto instanceof Adjunto
            && $this->user()?->can('update', $aula) === true
            && $this->user()?->can('update', $adjunto) === true;
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        return [
            'observaciones' => ['present', 'nullable', 'string', 'max:2000'],
        ];
    }
}
