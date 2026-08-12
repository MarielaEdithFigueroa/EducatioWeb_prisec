<?php

namespace App\Policies;

use App\Models\PlanEstudio;
use App\Models\User;

class PlanEstudioPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, PlanEstudio $planEstudio): bool
    {
        return true;
    }
}
