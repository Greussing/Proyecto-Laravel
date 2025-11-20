<?php

namespace Tests\Feature;

use App\Enums\MetodoPago;
use App\Enums\VentaEstado;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_puede_crear_venta_si_hay_stock()
    {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $producto = Producto::factory()->create(['cantidad' => 10, 'precio' => 100]);

        $response = $this->actingAs($user)->post(route('ventas.store'), [
            'cliente' => $cliente->id,
            'producto' => $producto->id,
            'cantidad' => 2,
            'precio_unitario' => 100,
            'metodo_pago' => MetodoPago::EFECTIVO->value,
            'estado' => VentaEstado::PAGADO->value,
            'fecha' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('ventas.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ventas', ['cliente' => $cliente->id]);
        $this->assertEquals(8, $producto->fresh()->cantidad);
    }

    public function test_no_puede_crear_venta_sin_stock()
    {
        $user = User::factory()->create();
        $cliente = Cliente::factory()->create();
        $producto = Producto::factory()->create(['cantidad' => 1]);

        $response = $this->actingAs($user)->post(route('ventas.store'), [
            'cliente' => $cliente->id,
            'producto' => $producto->id,
            'cantidad' => 5, // Excede stock
            'precio_unitario' => 100,
            'metodo_pago' => MetodoPago::EFECTIVO->value,
            'estado' => VentaEstado::PAGADO->value,
            'fecha' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors('error');
        $this->assertEquals(1, $producto->fresh()->cantidad);
    }
}
