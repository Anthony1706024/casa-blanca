<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Subcategoria;
use App\Services\CartaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JarrasCoctelesTest extends TestCase
{
    use RefreshDatabase;

    private function migrar(): void
    {
        (require database_path('migrations/2026_11_22_000006_agregar_jarras_y_cocteles.php'))->up();
    }

    private function sistemaConCarta(): Categoria
    {
        $bebidas = Categoria::create(['nombre' => 'Bebidas']);
        Producto::create([
            'codigo' => 'BEB013', 'nombre' => 'Coca Cola 1.5L', 'tipo' => 'directo', 'stock' => 10, 'precio_venta' => 12,
            'categoria_id' => $bebidas->id,
            'subcategoria_id' => Subcategoria::create(['nombre' => 'Gaseosas y Aguas', 'categoria_id' => $bebidas->id])->id,
        ]);

        return $bebidas;
    }

    public function test_en_una_base_vacia_no_agrega_nada(): void
    {
        $categorias = Categoria::count();

        $this->migrar();

        $this->assertSame(0, Producto::count());
        $this->assertSame($categorias, Categoria::count());
    }

    public function test_agrega_jarras_frozen_y_cocteles_con_receta_foto_y_precio(): void
    {
        $bebidas = $this->sistemaConCarta();

        $this->migrar();

        $this->assertSame(26, Producto::count(), '25 bebidas nuevas + la gaseosa que ya estaba');

        $jarra = Producto::with('insumos', 'subcategoria')->where('nombre', 'Jarra de chicha morada')->sole();
        $this->assertSame($bebidas->id, $jarra->categoria_id);
        $this->assertSame('Jugos Naturales', $jarra->subcategoria->nombre);
        $this->assertSame('BEB015', $jarra->codigo, 'Sigue después del último código BEB que había');
        $this->assertEquals(20, $jarra->precio_venta);
        $this->assertSame('bebidas', $jarra->cocina);
        $this->assertSame('productos/41a51e65-d2d5-42ca-8ac9-7fa749b0f765.webp', $jarra->imagen);
        $this->assertContains('Maíz morado', $jarra->insumos->pluck('nombre'));

        $this->assertEquals(25, Producto::where('nombre', 'Maracuyá Frozen')->value('precio_venta'));

        $pisco = Producto::with('insumos', 'categoria')->where('nombre', 'Pisco Sour')->sole();
        $this->assertSame('Cócteles', $pisco->categoria->nombre);
        $this->assertSame('COC001', $pisco->codigo);
        $this->assertEquals(28, $pisco->precio_venta);
        $this->assertContains('Pisco', $pisco->insumos->pluck('nombre'));

        $sangria = Producto::with('insumos')->where('nombre', 'Jarra de sangría')->sole();
        $this->assertEquals(70, $sangria->precio_venta);
        $this->assertContains('Vino tinto', $sangria->insumos->pluck('nombre'));

        // Todas se venden directo con 60 unidades cada una; la receta queda solo para "Qué trae"; las fotos existen
        Producto::with('insumos')->where('codigo', '!=', 'BEB013')->get()->each(function ($producto) {
            $this->assertSame(Producto::DIRECTO, $producto->tipo, $producto->nombre);
            $this->assertSame(60, $producto->stock, $producto->nombre);
            $this->assertSame(60, $producto->disponible, $producto->nombre);
            $this->assertNotEmpty($producto->insumos, "{$producto->nombre} sin receta");
            $this->assertFileExists(storage_path('app/public/'.$producto->imagen));
        });
        $this->assertNotEmpty($pisco->incluye, 'La carta muestra qué trae el Pisco Sour');
    }

    public function test_respeta_lo_que_ya_existe_y_no_duplica(): void
    {
        $this->sistemaConCarta();
        $cocteles = Categoria::create(['nombre' => 'Cócteles']);
        Producto::create([
            'codigo' => 'COC007', 'nombre' => 'PISCO SOUR', 'tipo' => 'directo', 'stock' => 5, 'precio_venta' => 30,
            'categoria_id' => $cocteles->id,
        ]);

        $this->migrar();
        $this->migrar();

        $this->assertSame(26, Producto::count(), 'El Pisco Sour existente no se repite y la segunda vez no agrega nada');
        $this->assertSame(1, Categoria::where('nombre', 'Cócteles')->count());
        $this->assertEquals(30, Producto::where('codigo', 'COC007')->value('precio_venta'), 'No cambia el precio del que ya estaba');
        $this->assertSame('COC008', Producto::where('nombre', 'Piña Colada')->value('codigo'));
    }

    public function test_la_carta_muestra_jugos_y_cocteles_en_su_seccion(): void
    {
        $this->sistemaConCarta();
        $this->migrar();

        $secciones = collect(app(CartaService::class)->secciones(Producto::disponiblesParaVenta()))
            ->mapWithKeys(fn ($seccion) => [$seccion['chip'] => $seccion]);

        // Gaseosas, luego jugos y luego cócteles, cada uno con lo suyo
        $this->assertSame(['Gaseosas', 'Jugos y frozen', 'Cócteles'], $secciones->keys()->all());
        $this->assertStringContainsString('Coca Cola 1.5L', json_encode($secciones['Gaseosas'], JSON_UNESCAPED_UNICODE));
        $this->assertStringNotContainsString('Jarra', json_encode($secciones['Gaseosas'], JSON_UNESCAPED_UNICODE));
        $this->assertStringContainsString('Limonada Frozen', json_encode($secciones['Jugos y frozen'], JSON_UNESCAPED_UNICODE));
        $this->assertStringContainsString('Jarra de sangría', json_encode($secciones['Cócteles'], JSON_UNESCAPED_UNICODE));

        $this->get(route('carta.index'))->assertOk()->assertSee('Pisco Sour')->assertSee('Jarra de chicha morada');
    }
}
