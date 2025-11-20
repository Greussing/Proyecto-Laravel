<?php

namespace Tests\Unit\Services;

use App\Enums\MetodoPago;
use App\Enums\VentaEstado;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use App\Services\StockService;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class VentaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_venta_descuenta_stock_correctamente()
    {
        // Arrange
        $stockServiceMock = Mockery::mock(StockService::class);
        $service = new VentaService($stockServiceMock);

        $producto = Producto::factory()->create(['cantidad' => 10, 'precio' => 100]);
        $user = User::factory()->create();
        $cliente = \App\Models\Cliente::factory()->create();

        $data = [
            'producto' => $producto->id,
            'cantidad' => 2,
            'precio_unitario' => 100,
            'cliente' => $cliente->id,
            'metodo_pago' => MetodoPago::EFECTIVO->value,
            'estado' => VentaEstado::PAGADO->value,
            'fecha' => now(),
        ];

        $this->actingAs($user);

        // Act
        $venta = $service->createVenta($data);

        // Assert
        $this->assertDatabaseHas('ventas', ['id' => $venta->id, 'total' => 200]);
        $this->assertDatabaseHas('detalle_ventas', ['venta_id' => $venta->id, 'cantidad' => 2]);
        
        // Verificar que el stock se descontó (esto lo hace el observer de DetalleVenta en realidad, 
        // pero el servicio orquesta la creación que dispara el observer)
        $this->assertEquals(8, $producto->fresh()->cantidad);
    }

    public function test_create_venta_lanza_excepcion_si_no_hay_stock()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Stock insuficiente');

        $stockServiceMock = Mockery::mock(StockService::class);
        $service = new VentaService($stockServiceMock);

        $producto = Producto::factory()->create(['cantidad' => 1]);
        $cliente = \App\Models\Cliente::factory()->create();

        $data = [
            'producto' => $producto->id,
            'cantidad' => 5, // Pide más de lo que hay
            'precio_unitario' => 100,
            'cliente' => $cliente->id,
            'metodo_pago' => MetodoPago::EFECTIVO->value,
            'estado' => VentaEstado::PAGADO->value,
            'fecha' => now(),
        ];

        $service->createVenta($data);
    }

    public function test_delete_venta_repone_stock()
    {
        $stockServiceMock = Mockery::mock(StockService::class);
        $service = new VentaService($stockServiceMock);
        $user = User::factory()->create();
        $this->actingAs($user);

        // Crear venta inicial
        $producto = Producto::factory()->create(['cantidad' => 10]);
        $venta = Venta::factory()->create();
        $detalle = DetalleVenta::factory()->create([
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'cantidad' => 2
        ]);
        
        // Simular que al crear el detalle se descontó stock (manualmente para el setup)
        $producto->decrement('cantidad', 2); 
        $this->assertEquals(8, $producto->fresh()->cantidad);

        // Act
        $service->deleteVenta($venta);

        // Assert
        $this->assertSoftDeleted($venta); // Si usa soft deletes, o assertDatabaseMissing si no
        // El observer deleting de DetalleVenta debería reponer el stock
        $this->assertEquals(10, $producto->fresh()->cantidad);
    }
}
