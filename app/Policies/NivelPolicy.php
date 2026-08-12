<?php

namespace App\Policies;

use App\Models\Nivel;
use App\Models\User;

class NivelPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Nivel $nivel): bool
    {
        return true;
    }
}
