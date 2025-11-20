<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venta;

class VentaPolicy
{
    /**
     * Cualquiera puede ver el listado.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Cualquiera puede ver una venta específica.
     */
    public function view(User $user, Venta $venta): bool
    {
        return true;
    }

    /**
     * Cualquiera puede crear ventas (vendedores).
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Solo admin puede actualizar (editar) ventas ya hechas.
     * Opcional: permitir al dueño si es reciente, pero por regla general admin.
     */
    public function update(User $user, Venta $venta): bool
    {
        return $user->isAdmin();
    }

    /**
     * Solo admin puede eliminar/anular ventas.
     */
    public function delete(User $user, Venta $venta): bool
    {
        return $user->isAdmin();
    }
}
