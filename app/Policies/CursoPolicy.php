<?php

namespace App\Policies;

use App\Models\Curso;
use App\Models\User;

/**
 * Autorización diferida (ver core.md): hoy todo usuario autenticado puede todo.
 * La costura queda para cuando existan roles/permisos: un solo punto de cambio.
 */
class CursoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Curso $curso): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Curso $curso): bool
    {
        return true;
    }

    public function delete(User $user, Curso $curso): bool
    {
        return true;
    }
}
