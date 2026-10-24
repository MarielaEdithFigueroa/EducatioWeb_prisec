<?php

namespace App\Policies;

use App\Models\Adjunto;
use App\Models\User;

class AdjuntoPolicy
{
    public function view(User $user, Adjunto $adjunto): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Adjunto $adjunto): bool
    {
        return true;
    }
}
