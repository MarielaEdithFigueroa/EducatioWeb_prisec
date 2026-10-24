<?php

namespace App\Rules;

use App\Services\TelefonoNormalizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class Telefono implements ValidationRule
{
    public function __construct(private readonly bool $esLineaFija = false) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! TelefonoNormalizer::esValido($value)) {
            $fail('El :attribute debe ser un número de teléfono válido.');

            return;
        }

        if ($this->esLineaFija && TelefonoNormalizer::esMovilArgentina($value)) {
            $fail('El número ingresado parece ser móvil. Cambiá el tipo de línea o revisá el número.');
        }
    }
}
