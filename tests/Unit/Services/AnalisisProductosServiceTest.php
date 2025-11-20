<?php

namespace Tests\Unit\Services;

use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\AnalisisProductosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalisisProductosServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_calcula_metricas_correctamente()
    {
        $service = new AnalisisProductosService();

        // Crear datos
        $producto = Producto::factory()->create(['nombre' => 'Prod A', 'cantidad' => 100]);
        
        // Venta hace 5 días
        $venta = Venta::factory()->create(['fecha' => now()->subDays(5)]);
        DetalleVenta::factory()->create([
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'cantidad' => 10,
            'subtotal' => 1000,
        ]);

        // Act
        [$stats, $ingresoTotal] = $service->getStats(30);

        // Assert
        $statProducto = $stats->firstWhere('producto', 'Prod A');
        
        $this->assertNotNull($statProducto);
        $this->assertEquals(10, $statProducto->vendido);
        $this->assertEquals(1000, $statProducto->ingreso_total);
        $this->assertEquals('Media', $statProducto->rotacion); // 10 vendido = Media (<=20)
        
        $this->assertEquals(1000, $ingresoTotal);
    }
}
