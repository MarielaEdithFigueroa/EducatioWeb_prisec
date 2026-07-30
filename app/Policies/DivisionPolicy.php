<?php

namespace App\Policies;

use App\Models\Division;
use App\Models\User;

/**
 * Autorización diferida (ver core.md): hoy todo usuario autenticado puede todo.
 * La costura queda para cuando existan roles/permisos: un solo punto de cambio.
 */
class DivisionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Division $division): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Division $division): bool
    {
        return true;
    }

    public function delete(User $user, Division $division): bool
    {
        return true;
    }
}
