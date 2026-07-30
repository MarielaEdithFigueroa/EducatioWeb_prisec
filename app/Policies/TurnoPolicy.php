<?php

namespace App\Policies;

use App\Models\Turno;
use App\Models\User;

/**
 * Autorización diferida (ver core.md): hoy todo usuario autenticado puede todo.
 * La costura queda para cuando existan roles/permisos: un solo punto de cambio.
 */
class TurnoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Turno $turno): bool
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

    public function delete(User $user, Turno $turno): bool
    {
        return true;
    }
}
