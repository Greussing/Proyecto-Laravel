<?php

namespace App\Policies;

use App\Models\Producto;
use App\Models\User;

class ProductoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Producto $producto): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Producto $producto): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Producto $producto): bool
    {
        return $user->isAdmin();
    }
}
