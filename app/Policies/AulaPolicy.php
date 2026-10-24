<?php

namespace App\Policies;

use App\Models\Aula;
use App\Models\User;

class AulaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Aula $aula): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Aula $aula): bool
    {
        return true;
    }
}
