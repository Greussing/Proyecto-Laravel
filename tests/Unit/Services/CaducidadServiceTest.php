<?php

namespace Tests\Unit\Services;

use App\Models\Producto;
use App\Services\CaducidadService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaducidadServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_clasifica_productos_correctamente()
    {
        // Fijar fecha "hoy"
        Carbon::setTestNow('2025-01-01');

        // Crear productos
        $vencido = Producto::factory()->create(['fecha_vencimiento' => '2024-12-31']);
        $proximo = Producto::factory()->create(['fecha_vencimiento' => '2025-01-15']); // < 30 días
        $revision = Producto::factory()->create(['fecha_vencimiento' => '2025-02-15']); // > 30 y <= 60 días
        $lejano = Producto::factory()->create(['fecha_vencimiento' => '2025-06-01']);

        $service = new CaducidadService();

        // Act
        [$proximos, $vencidos, $revisar] = $service->getReporteCaducidad();

        // Assert
        $this->assertTrue($vencidos->contains($vencido));
        $this->assertTrue($proximos->contains($proximo));
        $this->assertTrue($revisar->contains($revision));
        
        $this->assertFalse($vencidos->contains($proximo));
        $this->assertFalse($proximos->contains($lejano));
    }
}
