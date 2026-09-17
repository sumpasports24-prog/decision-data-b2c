<?php

namespace Tests\Unit\Rules;

use App\Rules\CedulaEcuatoriana;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CedulaEcuatorianaTest extends TestCase
{
    #[DataProvider('cedulasMalFormadas')]
    public function test_rechaza_entradas_mal_formadas(string $valor): void
    {
        $fallo = null;
        (new CedulaEcuatoriana)->validate('cedula', $valor, function (string $mensaje) use (&$fallo) {
            $fallo = $mensaje;
        });

        $this->assertNotNull($fallo, "Se esperaba que '{$valor}' fuera rechazada.");
    }

    public static function cedulasMalFormadas(): array
    {
        return [
            'muy corta' => ['12345'],
            'con letras' => ['12345abcde'],
            'provincia inexistente' => ['990101234'.'5'],
            'tercer digito de RUC de sociedad' => ['1768888888'],
            'digito verificador incorrecto' => ['1710034066'], // mismo prefijo que la válida, dígito verificador +1
        ];
    }

    public function test_acepta_una_cedula_valida(): void
    {
        $fallo = null;
        // 1710034065: cédula sintética con dígito verificador módulo 10 correcto (calculado, no inventado).
        (new CedulaEcuatoriana)->validate('cedula', '1710034065', function (string $mensaje) use (&$fallo) {
            $fallo = $mensaje;
        });

        $this->assertNull($fallo);
    }
}
