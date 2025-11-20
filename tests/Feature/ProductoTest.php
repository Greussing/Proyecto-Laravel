<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_muestra_productos()
    {
        $user = User::factory()->create();
        $producto = Producto::factory()->create(['nombre' => 'Laptop Gamer']);

        $response = $this->actingAs($user)->get(route('productos.index'));

        $response->assertStatus(200);
        $response->assertSee('Laptop Gamer');
    }

    public function test_filtro_por_categoria()
    {
        $user = User::factory()->create();
        $cat1 = Categoria::factory()->create(['nombre' => 'Electrónica']);
        $cat2 = Categoria::factory()->create(['nombre' => 'Ropa']);

        $prod1 = Producto::factory()->create(['categoria' => $cat1->id, 'nombre' => 'TV']);
        $prod2 = Producto::factory()->create(['categoria' => $cat2->id, 'nombre' => 'Camisa']);

        $response = $this->actingAs($user)->get(route('productos.index', ['categoria' => $cat1->id]));

        $response->assertSee('TV');
        $response->assertDontSee('Camisa');
    }
}
