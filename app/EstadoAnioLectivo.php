<?php

namespace App;

enum EstadoAnioLectivo: string
{
    case Preparacion = 'preparacion';
    case Vigente = 'vigente';
    case Cerrado = 'cerrado';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Preparacion => 'En preparación',
            self::Vigente => 'Vigente',
            self::Cerrado => 'Cerrado',
        };
    }
}
