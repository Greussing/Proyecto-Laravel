<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_ventas_pdf_funciona()
    {
        $user = User::factory()->create();
        // Crear datos para que no esté vacío
        \App\Models\Venta::factory()->create();

        $response = $this->actingAs($user)->get('/ventas/pdf'); // Ajustar ruta si es diferente

        // Puede que sea una descarga directa
        $response->assertStatus(200);
        // Verificar header de descarga
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_export_productos_excel_funciona()
    {
        $user = User::factory()->create();
        \App\Models\Producto::factory()->create();

        $response = $this->actingAs($user)->get('/productos/excel'); // Ajustar ruta

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
