<?php

namespace App\Policies;

use App\Models\TipoDocumentacionIntegrante;
use App\Models\User;

class TipoDocumentacionIntegrantePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TipoDocumentacionIntegrante $tipoDocumentacionIntegrante): bool
    {
        return true;
    }
}
