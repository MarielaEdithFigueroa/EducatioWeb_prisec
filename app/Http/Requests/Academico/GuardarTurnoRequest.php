<?php

namespace App\Http\Requests\Academico;

use App\Models\Turno;
use Illuminate\Database\Eloquent\Model;

class GuardarTurnoRequest extends GuardarCatalogoConNivelRequest
{
    protected function tabla(): string
    {
        return 'turnos';
    }

    /** @return class-string<Model> */
    protected function claseModelo(): string
    {
        return Turno::class;
    }

    protected function parametroRuta(): string
    {
        return 'turno';
    }
}
