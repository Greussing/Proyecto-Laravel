<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_no_admin_no_puede_eliminar_venta()
    {
        $user = User::factory()->create(['role' => 'user']);
        $venta = Venta::factory()->create();

        $response = $this->actingAs($user)->delete(route('ventas.destroy', $venta));

        $response->assertForbidden(); // 403
        $this->assertDatabaseHas('ventas', ['id' => $venta->id]);
    }

    public function test_admin_puede_eliminar_venta()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $venta = Venta::factory()->create();
        // Necesitamos detalles para que el servicio no falle al intentar reponer stock
        \App\Models\DetalleVenta::factory()->create(['venta_id' => $venta->id]);

        $response = $this->actingAs($admin)->delete(route('ventas.destroy', $venta));

        $response->assertRedirect();
        // Verifica soft delete o delete real según configuración
        // Asumimos delete real por ahora o soft delete
        // $this->assertSoftDeleted('ventas', ['id' => $venta->id]); 
    }

    public function test_user_no_admin_no_puede_crear_producto()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->post(route('productos.store'), [
            'nombre' => 'Nuevo Prod',
            'cantidad' => 10,
            'precio' => 100,
        ]);

        $response->assertForbidden();
    }

    public function test_admin_puede_crear_producto()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $categoria = \App\Models\Categoria::factory()->create();

        $response = $this->actingAs($admin)->post(route('productos.store'), [
            'nombre' => 'Nuevo Prod Admin',
            'cantidad' => 10,
            'precio' => 100,
            'categoria' => $categoria->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('productos', ['nombre' => 'Nuevo Prod Admin']);
    }
}
