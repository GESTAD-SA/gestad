<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/utils/ColombiaValidator.php';

class ColombiaValidatorTest extends TestCase
{
    public function testValidCedulaWith10Digits()
    {
        $result = ColombiaValidator::validarCedula('1234567890');
        $this->assertTrue($result['isValid']);
        $this->assertEquals('Cédula válida.', $result['message']);
    }

    public function testValidCedulaWithSpaces()
    {
        $result = ColombiaValidator::validarCedula('123 456 7890');
        $this->assertTrue($result['isValid']);
        $this->assertEquals('Cédula válida.', $result['message']);
    }

    public function testInvalidCedulaWith8Digits()
    {
        $result = ColombiaValidator::validarCedula('12345678');
        $this->assertFalse($result['isValid']);
        $this->assertEquals('La cédula debe tener exactamente 10 dígitos.', $result['message']);
    }

    public function testInvalidCedulaWithLetters()
    {
        $result = ColombiaValidator::validarCedula('12345abc90');
        $this->assertFalse($result['isValid']);
        $this->assertEquals('La cédula debe tener exactamente 10 dígitos.', $result['message']);
    }

    public function testInvalidCedulaWithSpecialCharacters()
    {
        $result = ColombiaValidator::validarCedula('12345-7890');
        $this->assertFalse($result['isValid']);
        $this->assertEquals('La cédula debe tener exactamente 10 dígitos.', $result['message']);
    }

    public function testInvalidCedulaWithMoreThan10Digits()
    {
        $result = ColombiaValidator::validarCedula('123456789012');
        $this->assertFalse($result['isValid']);
        $this->assertEquals('La cédula debe tener exactamente 10 dígitos.', $result['message']);
    }

    public function testInvalidCedulaEmpty()
    {
        $result = ColombiaValidator::validarCedula('');
        $this->assertFalse($result['isValid']);
        $this->assertEquals('La cédula debe tener exactamente 10 dígitos.', $result['message']);
    }
}
