<?php

namespace Tests\Feature;

use App\Models\MovimientoStock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovimientoStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_muestra_movimientos()
    {
        $user = User::factory()->create();
        MovimientoStock::factory()->create(['detalle' => 'Ajuste inventario']);

        $response = $this->actingAs($user)->get(route('movimientos.index'));

        $response->assertStatus(200);
        $response->assertSee('Ajuste inventario');
    }
}
