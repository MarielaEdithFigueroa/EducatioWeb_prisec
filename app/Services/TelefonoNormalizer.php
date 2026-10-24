<?php

namespace App\Services;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

final class TelefonoNormalizer
{
    public static function normalizarArgentina(?string $telefono, bool $esLineaFija = false): ?string
    {
        $phoneNumberUtil = PhoneNumberUtil::getInstance();
        $numero = self::parsear($telefono);

        if ($numero === null) {
            return null;
        }

        $numeroNormalizado = $phoneNumberUtil->format($numero, PhoneNumberFormat::E164);

        if ($numero->getCountryCode() !== 54) {
            return $numeroNormalizado;
        }

        $tipo = $phoneNumberUtil->getNumberType($numero);

        if ($esLineaFija && $tipo === PhoneNumberType::MOBILE) {
            return null;
        }

        if (! in_array($tipo, [PhoneNumberType::FIXED_LINE, PhoneNumberType::MOBILE, PhoneNumberType::FIXED_LINE_OR_MOBILE, PhoneNumberType::UNKNOWN], true)) {
            return $numeroNormalizado;
        }

        $numeroNacional = $phoneNumberUtil->getNationalSignificantNumber($numero);

        if (str_starts_with($numeroNormalizado, '+549') && str_starts_with($numeroNacional, '9')) {
            $numeroNacional = substr($numeroNacional, 1);
        }

        $prefijo = $esLineaFija ? '+54' : '+549';

        try {
            $candidato = $phoneNumberUtil->parse($prefijo.$numeroNacional);
        } catch (NumberParseException) {
            return null;
        }

        if (! $phoneNumberUtil->isPossibleNumber($candidato)) {
            return null;
        }

        return $phoneNumberUtil->format($candidato, PhoneNumberFormat::E164);
    }

    public static function esPosible(?string $telefono): bool
    {
        return self::parsear($telefono) !== null;
    }

    public static function esValido(?string $telefono): bool
    {
        $numero = self::parsear($telefono);

        if ($numero === null) {
            return false;
        }

        return PhoneNumberUtil::getInstance()->isValidNumber($numero);
    }

    public static function esMovilArgentina(?string $telefono): bool
    {
        $numero = self::parsear($telefono);

        if ($numero === null || $numero->getCountryCode() !== 54) {
            return false;
        }

        return PhoneNumberUtil::getInstance()->getNumberType($numero) === PhoneNumberType::MOBILE;
    }

    public static function esLineaFija(?string $telefono): bool
    {
        $numero = self::parsear($telefono);

        if ($numero === null) {
            return false;
        }

        $phoneNumberUtil = PhoneNumberUtil::getInstance();

        if ($numero->getCountryCode() !== 54) {
            return $phoneNumberUtil->getNumberType($numero) === PhoneNumberType::FIXED_LINE;
        }

        $numeroNormalizado = $phoneNumberUtil->format($numero, PhoneNumberFormat::E164);

        return str_starts_with($numeroNormalizado, '+54') && ! str_starts_with($numeroNormalizado, '+549');
    }

    public static function soloDigitos(?string $telefono): string
    {
        return preg_replace('/\D/', '', (string) $telefono) ?? '';
    }

    private static function parsear(?string $telefono): ?PhoneNumber
    {
        $telefono = trim((string) $telefono);

        if ($telefono === '') {
            return null;
        }

        $phoneNumberUtil = PhoneNumberUtil::getInstance();

        try {
            $numero = $phoneNumberUtil->parse($telefono, 'AR');
        } catch (NumberParseException) {
            return null;
        }

        if ($numero->hasExtension() || ! $phoneNumberUtil->isPossibleNumber($numero)) {
            return null;
        }

        return $numero;
    }
}
