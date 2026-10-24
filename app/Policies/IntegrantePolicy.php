<?php

namespace App\Policies;

use App\Models\Integrante;
use App\Models\User;

class IntegrantePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Integrante $integrante): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Integrante $integrante): bool
    {
        return true;
    }
}
