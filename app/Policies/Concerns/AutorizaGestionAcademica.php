<?php

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait AutorizaGestionAcademica
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Model $modelo): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Model $modelo): bool
    {
        return true;
    }

    public function deactivate(User $user, Model $modelo): bool
    {
        return true;
    }

    public function reactivate(User $user, Model $modelo): bool
    {
        return true;
    }

    public function projectGroups(User $user, Model $modelo): bool
    {
        return true;
    }

    public function markAsCurrent(User $user, Model $modelo): bool
    {
        return true;
    }
}
