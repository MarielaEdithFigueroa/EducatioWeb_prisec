<?php

namespace App\Http\Requests\Academico;

use App\Models\Division;
use Illuminate\Database\Eloquent\Model;

class GuardarDivisionRequest extends GuardarCatalogoConNivelRequest
{
    protected function tabla(): string
    {
        return 'divisiones';
    }

    /** @return class-string<Model> */
    protected function claseModelo(): string
    {
        return Division::class;
    }

    protected function parametroRuta(): string
    {
        return 'division';
    }
}
