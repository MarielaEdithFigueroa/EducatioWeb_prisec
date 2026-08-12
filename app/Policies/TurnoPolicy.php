<?php

namespace App\Policies;

use App\Models\Turno;
use App\Models\User;

class TurnoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Turno $turno): bool
    {
        return true;
    }
}
