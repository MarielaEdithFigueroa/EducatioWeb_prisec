<?php

namespace App\Policies;

use App\Models\Ciudad;
use App\Models\User;

class CiudadPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Ciudad $ciudad): bool
    {
        return true;
    }
}
