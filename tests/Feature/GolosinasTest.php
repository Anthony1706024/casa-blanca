<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Services\CartaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GolosinasTest extends TestCase
{
    use RefreshDatabase;

    private function migrar(): void
    {
        (require database_path('migrations/2026_11_22_000007_agregar_golosinas.php'))->up();
    }

    private function sistemaConCarta(): void
    {
        Producto::create([
            'codigo' => 'BEB001', 'nombre' => 'Inca Kola 500ml', 'tipo' => 'directo', 'stock' => 10, 'precio_venta' => 4,
            'categoria_id' => Categoria::create(['nombre' => 'Bebidas'])->id,
        ]);
    }

    public function test_en_una_base_vacia_no_agrega_nada(): void
    {
        $this->migrar();

        $this->assertSame(0, Producto::count());
        $this->assertNull(Categoria::where('nombre', 'Golosinas')->first());
    }

    public function test_agrega_las_golosinas_con_su_precio_y_60_de_stock(): void
    {
        $this->sistemaConCarta();

        $this->migrar();
        $this->migrar();

        $golosinas = Producto::whereHas('categoria', fn ($q) => $q->where('nombre', 'Golosinas'))->get();
        $this->assertCount(12, $golosinas, 'La segunda vez no duplica');
        $golosinas->each(function ($producto) {
            $this->assertSame(Producto::DIRECTO, $producto->tipo, $producto->nombre);
            $this->assertSame(60, $producto->disponible, $producto->nombre);
            $this->assertSame('bebidas', $producto->cocina);
        });

        $precio = fn ($nombre) => (float) Producto::where('nombre', $nombre)->value('precio_venta');
        $this->assertSame(2.0, $precio('Galleta Soda'));
        $this->assertSame(2.0, $precio('Chupetín'));
        $this->assertSame(3.0, $precio('Frugos'));
        $this->assertSame(3.0, $precio('Galleta Ritz'));
        $this->assertSame(4.0, $precio('Sublime Sonrisas grande'));
        $this->assertSame('GOL001', Producto::where('nombre', 'Galleta Soda')->value('codigo'));
    }

    public function test_la_carta_tiene_el_boton_golosinas(): void
    {
        $this->sistemaConCarta();
        $this->migrar();

        $chips = collect(app(CartaService::class)->secciones(Producto::disponiblesParaVenta()))->pluck('chip');
        $this->assertContains('Golosinas', $chips);

        $this->get(route('carta.index'))->assertOk()->assertSee('Golosinas')->assertSee('Galleta Picaras');
    }
}
