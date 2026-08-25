<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class Cuit implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && strlen($value) === 11 && ctype_digit($value)) {

            $suma = 0;
            $pesos = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
            for ($i = 0; $i <= 9; $i++) {
                $suma = $value[$i] * $pesos[$i] + $suma;
            }
            $resto = $suma % 11;
            $digitoVerificador = (int) $value[10];
            if ($resto === 1) {
                $exito = false;
            } elseif ($resto === 0) {
                $exito = $digitoVerificador === 0;
            } else {
                $exito = $digitoVerificador === 11 - $resto;
            }
        } else {
            $exito = false;
        }

        if (! $exito) {
            $fail('El :attribute debe ser un CUIL/T válido.');
        }

    }
}
