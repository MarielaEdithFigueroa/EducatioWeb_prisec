<?php

namespace App\Http\Requests\Academico;

use App\Models\Adjunto;
use App\Models\Integrante;
use Illuminate\Foundation\Http\FormRequest;

class DocumentoIntegranteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $integrante = $this->route('integrante');

        return $integrante instanceof Integrante
            && $this->user()?->can('update', $integrante) === true
            && (! $this->isMethod('post') || $this->user()?->can('create', Adjunto::class) === true);
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'archivo' => $this->isMethod('post') ? ['required', ...self::reglasPdf()] : ['prohibited'],
            'adjunto_id' => ['present', 'nullable', 'integer', 'min:1'],
        ];
    }

    /** @return list<string> */
    public static function reglasPdf(): array
    {
        return ['file', 'mimes:pdf', 'extensions:pdf', 'max:'.config('adjuntos.max_kb')];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['archivo' => 'PDF', 'adjunto_id' => 'PDF actual'];
    }
}
