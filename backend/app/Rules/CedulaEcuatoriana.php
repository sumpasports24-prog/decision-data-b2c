<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida una cédula ecuatoriana de persona natural: 10 dígitos, código de
 * provincia 01-24, tercer dígito < 6, y dígito verificador módulo 10.
 */
class CedulaEcuatoriana implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^\d{10}$/', $value)) {
            $fail('El campo :attribute debe tener exactamente 10 dígitos numéricos.');

            return;
        }

        $provincia = (int) substr($value, 0, 2);

        if ($provincia < 1 || $provincia > 24) {
            $fail('El campo :attribute no tiene un código de provincia válido.');

            return;
        }

        $tercerDigito = (int) $value[2];

        if ($tercerDigito >= 6) {
            $fail('El campo :attribute no corresponde a una cédula de persona natural.');

            return;
        }

        if (! $this->digitoVerificadorValido($value)) {
            $fail('El campo :attribute no es una cédula válida.');
        }
    }

    private function digitoVerificadorValido(string $cedula): bool
    {
        $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        $digitoVerificador = (int) $cedula[9];
        $suma = 0;

        for ($i = 0; $i < 9; $i++) {
            $valor = (int) $cedula[$i] * $coeficientes[$i];
            $suma += $valor >= 10 ? $valor - 9 : $valor;
        }

        $resultado = (10 - ($suma % 10)) % 10;

        return $resultado === $digitoVerificador;
    }
}
