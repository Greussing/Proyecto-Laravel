<?php

namespace Tests\Unit\Services;

use App\Models\Producto;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_registrar_devolucion_incrementa_stock_y_crea_movimiento()
    {
        $service = new StockService();
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $producto = Producto::factory()->create(['cantidad' => 10]);
        $venta = Venta::factory()->create();
        $detalle = DetalleVenta::factory()->create([
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'cantidad' => 5
        ]);

        // Act
        $service->registrarDevolucion($venta, 2, 'Devolución parcial');

        // Assert
        $this->assertEquals(12, $producto->fresh()->cantidad); // 10 + 2
        
        $this->assertDatabaseHas('movimientos_stock', [
            'producto_id' => $producto->id,
            'venta_id' => $venta->id,
            'tipo' => 'devolucion',
            'cantidad' => 2,
            'detalle' => 'Devolución parcial',
        ]);
    }
}
