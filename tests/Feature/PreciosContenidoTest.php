<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\CategoriaInsumo;
use App\Models\Contenido;
use App\Models\Insumo;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoPrecio;
use App\Models\Recibo;
use App\Models\Role;
use App\Models\SitioItem;
use App\Models\User;
use App\Services\ImagenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PreciosContenidoTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $mozo;

    private User $cajera;

    private Producto $gaseosa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role_id' => Role::create(['nombre' => 'admin'])->id]);
        $this->mozo = User::factory()->create(['role_id' => Role::create(['nombre' => 'mozo'])->id]);
        $this->cajera = User::factory()->create(['role_id' => Role::create(['nombre' => 'cajera'])->id]);

        $this->gaseosa = Producto::create([
            'codigo' => 'B1', 'nombre' => 'Gaseosa', 'tipo' => 'directo',
            'categoria_id' => Categoria::create(['nombre' => 'Bebidas'])->id,
            'stock' => 100, 'precio_venta' => 3,
        ]);
    }

    private function venderGaseosas(int $cantidad, float $total): Recibo
    {
        $this->actingAs($this->mozo)->post(route('mozo.pedidos.store'), [
            'dni' => '12345678',
            'items' => [$this->gaseosa->id => $cantidad],
        ])->assertSessionHasNoErrors();

        $pedido = Pedido::latest('id')->firstOrFail();

        $this->actingAs($this->cajera)->post(route('cajera.pedidos.cobrar', $pedido->id), ['pagos' => [
            ['metodo' => 'efectivo', 'monto' => $total],
        ]])->assertSessionHasNoErrors();

        return $pedido->fresh('recibo')->recibo;
    }

    private function cambiarPrecio(float $precio): void
    {
        $this->actingAs($this->admin)->put(route('admin.precios.actualizar'), [
            'precios' => [$this->gaseosa->id => $precio],
        ])->assertSessionHasNoErrors();
    }

    public function test_subir_un_precio_no_cambia_las_ventas_anteriores(): void
    {
        // Ayer: 30 gaseosas a S/ 3
        $recibo = $this->venderGaseosas(30, 90);

        // Hoy sube a S/ 4
        $this->cambiarPrecio(4);

        $recibo->refresh();
        $item = $recibo->pedido->items->first();
        $this->assertEqualsWithDelta(90, (float) $recibo->total, 0.001);
        $this->assertEqualsWithDelta(3, (float) $item->precio_unitario, 0.001);
        $this->assertEqualsWithDelta(90, (float) $item->subtotal, 0.001);

        $this->actingAs($this->cajera)->get(route('recibos.show', $recibo->id))->assertOk()
            ->assertSee('S/ 3.00 c/u')
            ->assertSee('S/ 90.00')
            ->assertDontSee('S/ 4.00');

        // Las ventas nuevas ya salen a S/ 4
        $nuevo = $this->venderGaseosas(10, 40);
        $this->assertEqualsWithDelta(40, (float) $nuevo->total, 0.001);
    }

    public function test_un_pedido_pendiente_conserva_el_precio_con_el_que_se_registro(): void
    {
        $this->actingAs($this->mozo)->post(route('mozo.pedidos.store'), [
            'dni' => '12345678',
            'items' => [$this->gaseosa->id => 2],
        ]);
        $pedido = Pedido::firstOrFail();

        $this->cambiarPrecio(4);

        $this->actingAs($this->cajera)->post(route('cajera.pedidos.cobrar', $pedido->id), ['pagos' => [
            ['metodo' => 'efectivo', 'monto' => 6],
        ]])->assertSessionHasNoErrors();
    }

    public function test_el_reporte_separa_las_unidades_vendidas_a_cada_precio(): void
    {
        $this->venderGaseosas(30, 90);
        $this->cambiarPrecio(4);
        $this->venderGaseosas(10, 40);

        $respuesta = $this->actingAs($this->admin)->get(route('admin.precios.ventas'))->assertOk();

        $filas = $respuesta->viewData('filas');
        $this->assertCount(2, $filas);
        $this->assertEqualsWithDelta(130, (float) $respuesta->viewData('total'), 0.001);

        $aTres = $filas->firstWhere('precio_unitario', '3.00');
        $this->assertSame(30, (int) $aTres->unidades);
        $this->assertEqualsWithDelta(90, (float) $aTres->importe, 0.001);
        $this->assertEqualsWithDelta(4, (float) $aTres->precio_actual, 0.001);
    }

    public function test_el_reporte_no_cuenta_pedidos_sin_cobrar(): void
    {
        $this->actingAs($this->mozo)->post(route('mozo.pedidos.store'), [
            'dni' => '12345678',
            'items' => [$this->gaseosa->id => 2],
        ]);

        $respuesta = $this->actingAs($this->admin)->get(route('admin.precios.ventas'))->assertOk();
        $this->assertCount(0, $respuesta->viewData('filas'));
    }

    public function test_cada_cambio_de_precio_queda_registrado_con_su_autor(): void
    {
        $this->cambiarPrecio(4);

        $cambio = ProductoPrecio::firstOrFail();
        $this->assertEqualsWithDelta(3, (float) $cambio->precio_anterior, 0.001);
        $this->assertEqualsWithDelta(4, (float) $cambio->precio_nuevo, 0.001);
        $this->assertSame($this->admin->id, $cambio->user_id);

        $this->get(route('admin.precios.historial'))->assertOk()->assertSee('Gaseosa');
    }

    public function test_guardar_el_mismo_precio_no_crea_historial(): void
    {
        $this->cambiarPrecio(3);
        $this->assertSame(0, ProductoPrecio::count());
    }

    public function test_editar_el_precio_desde_el_formulario_de_producto_tambien_se_registra(): void
    {
        $this->actingAs($this->admin)->put(route('admin.almacen.update', $this->gaseosa->id), [
            'codigo' => 'B1',
            'nombre' => 'Gaseosa',
            'tipo' => 'directo',
            'categoria_id' => $this->gaseosa->categoria_id,
            'stock' => 100,
            'precio_venta' => 5,
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, ProductoPrecio::count());
    }

    public function test_solo_el_admin_gestiona_precios_y_contenido(): void
    {
        foreach ([$this->mozo, $this->cajera] as $usuario) {
            $this->actingAs($usuario)->get(route('admin.precios.index'))->assertForbidden();
            $this->actingAs($usuario)->get(route('admin.contenido.index'))->assertForbidden();
            $this->actingAs($usuario)->put(route('admin.precios.actualizar'), ['precios' => [$this->gaseosa->id => 1]])->assertForbidden();
        }

        $this->assertEqualsWithDelta(3, (float) $this->gaseosa->fresh()->precio_venta, 0.001);
    }

    public function test_el_admin_edita_los_textos_de_la_pagina(): void
    {
        $this->actingAs($this->admin)->get(route('admin.contenido.index'))->assertOk();

        $this->put(route('admin.contenido.guardar'), [
            'hero_titulo' => 'Te esperamos en Poroto',
            'whatsapp' => '51987654321',
        ])->assertSessionHasNoErrors();

        $this->get('/')->assertOk()
            ->assertSee('Te esperamos en Poroto')
            ->assertSee('wa.me/51987654321');
    }

    public function test_el_admin_cambia_la_imagen_de_portada_y_se_borra_la_anterior(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $this->put(route('admin.contenido.guardar'), [
            'hero_titulo' => 'Hola',
            'hero_imagen' => UploadedFile::fake()->image('a.jpg'),
        ])->assertSessionHasNoErrors();
        $primera = Contenido::where('clave', 'hero_imagen')->value('valor');
        Storage::disk('public')->assertExists($primera);

        $this->put(route('admin.contenido.guardar'), [
            'hero_titulo' => 'Hola',
            'hero_imagen' => UploadedFile::fake()->image('b.jpg'),
        ])->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($primera);
        Storage::disk('public')->assertExists(Contenido::where('clave', 'hero_imagen')->value('valor'));
    }

    public function test_el_admin_gestiona_instalaciones_con_imagen(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $this->post(route('admin.contenido.items.store', 'instalacion'), [
            'titulo' => 'Piscina grande',
            'descripcion' => 'Para toda la familia',
            'activo' => '1',
            'imagen' => UploadedFile::fake()->image('piscina.jpg'),
        ])->assertSessionHasNoErrors();

        $item = SitioItem::firstOrFail();
        Storage::disk('public')->assertExists($item->imagen);
        $this->get('/')->assertSee('Piscina grande')->assertSee($item->imagen);

        // Oculto: sigue guardado pero no se publica
        $this->put(route('admin.contenido.items.update', $item->id), ['titulo' => 'Piscina grande'])->assertSessionHasNoErrors();
        $this->get('/')->assertDontSee('Piscina grande');

        $this->get(route('admin.contenido.items.edit', $item->id))->assertOk();

        $this->delete(route('admin.contenido.items.destroy', $item->id));
        Storage::disk('public')->assertMissing($item->imagen);
        $this->assertSame(0, SitioItem::count());
    }

    public function test_un_archivo_que_no_es_imagen_se_rechaza(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post(route('admin.contenido.items.store', 'instalacion'), [
            'titulo' => 'Malo',
            'imagen' => UploadedFile::fake()->create('virus.php', 10, 'text/plain'),
        ])->assertSessionHasErrors('imagen');

        $this->assertSame(0, SitioItem::count());
    }

    public function test_un_tipo_de_seccion_desconocido_da_404(): void
    {
        $this->actingAs($this->admin)->get('/admin/contenido/secciones/otro')->assertNotFound();
    }

    public function test_la_carta_permite_ver_la_foto_del_producto_en_grande(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('productos/g.jpg', 'x');
        $this->gaseosa->update(['imagen' => 'productos/g.jpg']);

        $this->get(route('carta.index'))->assertOk()
            ->assertSee('data-visor-imagen', false)
            ->assertSee('data-visor-titulo="Gaseosa"', false)
            ->assertSee('id="visor"', false);
    }

    public function test_un_producto_sin_foto_tambien_abre_el_modal_con_precio_y_detalle(): void
    {
        $this->gaseosa->update(['descripcion' => 'Botella de 500 ml']);

        $this->get(route('carta.index'))->assertOk()
            ->assertSee('data-visor-titulo="Gaseosa"', false)
            ->assertSee('data-visor-precio="S/ 3.00"', false)
            ->assertSee('data-visor-texto="Botella de 500 ml"', false)
            ->assertDontSee('data-visor-imagen=', false);
    }

    public function test_el_admin_cambia_lo_que_trae_un_producto_y_se_ve_en_la_carta(): void
    {
        $this->actingAs($this->admin)->put(route('admin.precios.actualizar'), [
            'precios' => [$this->gaseosa->id => 3],
            'descripciones' => [$this->gaseosa->id => 'Viene con papa y menestra'],
        ])->assertSessionHasNoErrors();

        $this->assertSame('Viene con papa y menestra', $this->gaseosa->fresh()->descripcion);
        $this->assertSame(0, ProductoPrecio::count());

        $this->get(route('carta.index'))->assertSee('data-visor-texto="Viene con papa y menestra"', false);
    }

    private function platoDePato(): Producto
    {
        $categoria = CategoriaInsumo::create(['nombre' => 'Cocina']);
        $crear = fn ($nombre, $unidad) => Insumo::create(['nombre' => $nombre, 'categoria_insumo_id' => $categoria->id, 'unidad' => $unidad, 'stock' => 5000]);

        $plato = Producto::create(['codigo' => 'PT', 'nombre' => 'Arroz con pato', 'tipo' => 'plato', 'categoria_id' => $this->gaseosa->categoria_id, 'stock' => 0, 'precio_venta' => 36]);
        $plato->insumos()->attach([
            $crear('Pato', 'presa')->id => ['cantidad' => 1, 'visible_cliente' => true],
            $crear('Arroz', 'g')->id => ['cantidad' => 200, 'visible_cliente' => true],
            $crear('Papa', 'g')->id => ['cantidad' => 150, 'visible_cliente' => true],
            $crear('Aceite', 'ml')->id => ['cantidad' => 20, 'visible_cliente' => false],
        ]);

        return $plato;
    }

    public function test_el_cliente_ve_lo_que_trae_el_plato_segun_su_receta_sin_gramos(): void
    {
        $plato = $this->platoDePato()->fresh('insumos');

        $this->assertEqualsCanonicalizing(['1 presa de Pato', 'Arroz', 'Papa'], $plato->incluye);

        $html = $this->get(route('carta.index'))->assertOk()->getContent();
        $this->assertStringContainsString('1 presa de Pato', html_entity_decode($html));
        $this->assertStringNotContainsString('200', $this->atributoIncluye($html));
        $this->assertStringNotContainsString('Aceite', $this->atributoIncluye($html));
    }

    private function atributoIncluye(string $html): string
    {
        preg_match('/data-visor-incluye="([^"]*)"/', $html, $coincidencia);

        return html_entity_decode($coincidencia[1] ?? '');
    }

    public function test_un_insumo_oculto_al_cliente_igual_se_descuenta_del_stock(): void
    {
        $plato = $this->platoDePato();
        $aceite = Insumo::where('nombre', 'Aceite')->first();

        $this->post(route('carta.store'), ['dni' => '12345678', 'mesa' => '3', 'items' => [$plato->id => 1]])->assertSessionHasNoErrors();
        $this->actingAs($this->cajera)->post(route('cajera.pedidos.cobrar', Pedido::first()->id), ['pagos' => [
            ['metodo' => 'efectivo', 'monto' => 36],
        ]])->assertSessionHasNoErrors();

        $this->assertEqualsWithDelta(4980, (float) $aceite->fresh()->stock, 0.001);
    }

    public function test_el_admin_elige_que_insumos_ve_el_cliente_al_editar_la_receta(): void
    {
        $plato = $this->platoDePato();
        $arroz = Insumo::where('nombre', 'Arroz')->first();
        $sal = Insumo::where('nombre', 'Aceite')->first();

        $this->actingAs($this->admin)->put(route('admin.almacen.update', $plato->id), [
            'codigo' => 'PT',
            'nombre' => 'Arroz con pato',
            'tipo' => 'plato',
            'categoria_id' => $plato->categoria_id,
            'precio_venta' => 36,
            'receta' => [
                ['insumo_id' => $arroz->id, 'cantidad' => 250, 'visible' => '1'],
                ['insumo_id' => $sal->id, 'cantidad' => 5],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(['Arroz'], $plato->fresh('insumos')->incluye);
        $this->get(route('admin.almacen.edit', $plato->id))->assertOk()->assertSee('El cliente lo ve');
    }

    public function test_guardar_solo_precios_no_borra_lo_que_trae_el_producto(): void
    {
        $this->gaseosa->update(['descripcion' => 'Botella de 500 ml']);

        $this->cambiarPrecio(4);

        $this->assertSame('Botella de 500 ml', $this->gaseosa->fresh()->descripcion);
    }

    public function test_la_portada_muestra_los_precios_reales_de_los_destacados(): void
    {
        $this->actingAs($this->admin)->put(route('admin.precios.actualizar'), [
            'precios' => [$this->gaseosa->id => 4.5],
            'destacados' => [$this->gaseosa->id => '1'],
        ])->assertSessionHasNoErrors();

        $this->get('/')->assertOk()->assertSee('Gaseosa')->assertSee('S/ 4.50');
    }

    public function test_al_subir_una_foto_de_producto_se_guarda_en_webp_con_miniatura(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->put(route('admin.almacen.update', $this->gaseosa->id), [
            'codigo' => 'B1',
            'nombre' => 'Gaseosa',
            'tipo' => 'directo',
            'categoria_id' => $this->gaseosa->categoria_id,
            'stock' => 100,
            'precio_venta' => 3,
            'imagen' => UploadedFile::fake()->image('foto.jpg', 2400, 1800),
        ])->assertSessionHasNoErrors();

        $ruta = $this->gaseosa->fresh()->imagen;
        $this->assertStringEndsWith('.webp', $ruta);

        $grande = getimagesizefromstring(Storage::disk('public')->get($ruta));
        $mini = getimagesizefromstring(Storage::disk('public')->get(ImagenService::rutaMiniatura($ruta)));
        $this->assertSame('image/webp', $grande['mime']);
        $this->assertSame(1200, $grande[0]);
        $this->assertSame(900, $grande[1]);
        $this->assertSame(240, $mini[0]);

        $this->assertStringContainsString('/mini/', $this->gaseosa->fresh()->imagen_mini_url);

        $this->gaseosa->fresh()->delete();
        Storage::disk('public')->assertMissing($ruta);
        Storage::disk('public')->assertMissing(ImagenService::rutaMiniatura($ruta));
    }

    public function test_cambiar_la_foto_de_un_producto_borra_la_anterior_y_su_miniatura(): void
    {
        Storage::fake('public');
        $datos = ['codigo' => 'B1', 'nombre' => 'Gaseosa', 'tipo' => 'directo', 'categoria_id' => $this->gaseosa->categoria_id, 'stock' => 100, 'precio_venta' => 3];

        $this->actingAs($this->admin)->put(route('admin.almacen.update', $this->gaseosa->id), [...$datos, 'imagen' => UploadedFile::fake()->image('a.png', 300, 300)]);
        $primera = $this->gaseosa->fresh()->imagen;

        $this->put(route('admin.almacen.update', $this->gaseosa->id), [...$datos, 'imagen' => UploadedFile::fake()->image('b.png', 300, 300)]);

        Storage::disk('public')->assertMissing($primera);
        Storage::disk('public')->assertMissing(ImagenService::rutaMiniatura($primera));
        Storage::disk('public')->assertExists($this->gaseosa->fresh()->imagen);
    }

    public function test_el_contenido_de_la_pagina_tambien_se_guarda_en_webp(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->post(route('admin.contenido.items.store', 'galeria'), [
            'titulo' => 'Piscina',
            'activo' => '1',
            'imagen' => UploadedFile::fake()->image('p.jpg', 1600, 900),
        ])->assertSessionHasNoErrors();

        $this->assertStringEndsWith('.webp', SitioItem::firstOrFail()->imagen);
    }

    public function test_un_png_transparente_conserva_su_transparencia(): void
    {
        Storage::fake('public');
        $lienzo = imagecreatetruecolor(50, 50);
        imagesavealpha($lienzo, true);
        imagealphablending($lienzo, false);
        imagefill($lienzo, 0, 0, imagecolorallocatealpha($lienzo, 0, 0, 0, 127));
        ob_start();
        imagepng($lienzo);

        $ruta = app(ImagenService::class)->guardarContenido(ob_get_clean(), 'web');

        $resultado = imagecreatefromstring(Storage::disk('public')->get($ruta));
        $this->assertGreaterThan(100, imagecolorat($resultado, 25, 25) >> 24 & 0x7F);
    }

    public function test_un_archivo_corrupto_no_se_guarda(): void
    {
        Storage::fake('public');

        $this->expectException(ValidationException::class);
        app(ImagenService::class)->guardarContenido('esto no es una imagen', 'web');
    }

    public function test_el_comando_convierte_las_imagenes_existentes_y_borra_las_originales(): void
    {
        Storage::fake('public');
        $archivo = UploadedFile::fake()->image('vieja.jpg', 800, 600);
        Storage::disk('public')->put('productos/vieja.jpg', $archivo->get());
        $this->gaseosa->update(['imagen' => 'productos/vieja.jpg']);
        $precio = $this->gaseosa->fresh()->precio_venta;

        $this->artisan('imagenes:convertir')->assertSuccessful();

        $ruta = $this->gaseosa->fresh()->imagen;
        $this->assertStringEndsWith('.webp', $ruta);
        Storage::disk('public')->assertExists($ruta);
        Storage::disk('public')->assertExists(ImagenService::rutaMiniatura($ruta));
        Storage::disk('public')->assertMissing('productos/vieja.jpg');
        $this->assertSame($precio, $this->gaseosa->fresh()->precio_venta);
        $this->assertSame(0, ProductoPrecio::count());

        // Ejecutarlo otra vez no cambia nada
        $this->artisan('imagenes:convertir')->assertSuccessful();
        $this->assertSame($ruta, $this->gaseosa->fresh()->imagen);
    }

    public function test_la_carta_tiene_la_seccion_de_porciones(): void
    {
        $porciones = Categoria::where('nombre', 'Porciones')->firstOrFail();
        Producto::create(['codigo' => 'PO1', 'nombre' => 'Porción de papas', 'tipo' => 'directo', 'categoria_id' => $porciones->id, 'stock' => 5, 'precio_venta' => 8]);

        // En la carta impresa la sección de porciones se llama "Guarniciones"
        $this->get(route('carta.index'))->assertOk()->assertSee('Guarniciones')->assertSee('Porción de papas');
    }
}
