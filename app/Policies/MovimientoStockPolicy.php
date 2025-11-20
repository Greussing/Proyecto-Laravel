<?php

namespace App\Policies;

use App\Models\MovimientoStock;
use App\Models\User;

class MovimientoStockPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MovimientoStock $movimiento): bool
    {
        return true;
    }

    // Generalmente los movimientos se crean por sistema (ventas), 
    // pero si hay creación manual (ajustes), solo admin.
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    // Los movimientos de stock no deberían editarse ni borrarse para mantener integridad,
    // pero si se permite, solo admin.
    public function update(User $user, MovimientoStock $movimiento): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, MovimientoStock $movimiento): bool
    {
        return $user->isAdmin();
    }
}
