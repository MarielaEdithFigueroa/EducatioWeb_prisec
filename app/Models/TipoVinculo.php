<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre'])]
class TipoVinculo extends Model
{
    protected $table = 'tipos_vinculo';

    public $timestamps = false;

    /** @return HasMany<AlumnoResponsable, $this> */
    public function alumnosResponsables(): HasMany
    {
        return $this->hasMany(AlumnoResponsable::class, 'vinculo_id');
    }
}
