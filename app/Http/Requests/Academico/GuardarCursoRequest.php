<?php

namespace App\Http\Requests\Academico;

use App\Models\Curso;
use Illuminate\Database\Eloquent\Model;

class GuardarCursoRequest extends GuardarCatalogoConNivelRequest
{
    protected function tabla(): string
    {
        return 'cursos';
    }

    /** @return class-string<Model> */
    protected function claseModelo(): string
    {
        return Curso::class;
    }

    protected function parametroRuta(): string
    {
        return 'curso';
    }
}
