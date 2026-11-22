<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\CategoriaInsumo;
use App\Models\Insumo;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Role;
use App\Models\Subcategoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class VentasTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $mozo;

    private User $cajera;

    private Insumo $arroz;

    private Insumo $pollo;

    private Producto $plato;

    private Producto $gaseosa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->usuarioConRol('admin');
        $this->mozo = $this->usuarioConRol('mozo');
        $this->cajera = $this->usuarioConRol('cajera');

        $categoriaInsumo = CategoriaInsumo::create(['nombre' => 'Abarrotes']);
        $this->arroz = Insumo::create(['nombre' => 'Arroz', 'categoria_insumo_id' => $categoriaInsumo->id, 'unidad' => 'kg', 'stock' => 1, 'stock_minimo' => 0.2]);
        $this->pollo = Insumo::create(['nombre' => 'Pollo', 'categoria_insumo_id' => $categoriaInsumo->id, 'unidad' => 'unidad', 'stock' => 10, 'stock_minimo' => 1]);

        $categoria = Categoria::create(['nombre' => 'Platos']);

        $this->plato = Producto::create(['codigo' => 'P1', 'nombre' => 'Arroz con pollo', 'tipo' => 'plato', 'categoria_id' => $categoria->id, 'stock' => 0, 'precio_venta' => 25]);
        $this->plato->insumos()->attach([
            $this->arroz->id => ['cantidad' => 0.3],
            $this->pollo->id => ['cantidad' => 1],
        ]);

        $this->gaseosa = Producto::create(['codigo' => 'B1', 'nombre' => 'Gaseosa', 'tipo' => 'directo', 'categoria_id' => $categoria->id, 'stock' => 5, 'precio_venta' => 5]);
    }

    private function usuarioConRol(string $rol): User
    {
        return User::factory()->create(['role_id' => Role::create(['nombre' => $rol])->id]);
    }

    private function pedidoPendiente(array $items = []): Pedido
    {
        $this->actingAs($this->mozo)->post(route('mozo.pedidos.store'), [
            'dni' => '12345678',
            'items' => $items ?: [$this->plato->id => 1, $this->gaseosa->id => 2],
        ])->assertSessionHasNoErrors();

        return Pedido::latest('id')->firstOrFail();
    }

    public function test_el_disponible_de_un_plato_depende_del_insumo_mas_escaso(): void
    {
        // 1 kg / 0.3 kg = 3 platos, aunque el pollo alcance para 10
        $this->assertSame(3, $this->plato->fresh('insumos')->disponible);
    }

    public function test_el_admin_crea_un_plato_con_receta(): void
    {
        $categoria = Categoria::first();

        $this->actingAs($this->admin)->post(route('admin.almacen.store'), [
            'codigo' => 'P2',
            'nombre' => 'Pollo solo',
            'tipo' => 'plato',
            'categoria_id' => $categoria->id,
            'precio_venta' => 20,
            'receta' => [['insumo_id' => $this->pollo->id, 'cantidad' => 1]],
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.almacen.index'));

        $producto = Producto::where('codigo', 'P2')->with('insumos')->first();
        $this->assertTrue($producto->esPlato());
        $this->assertSame(0, (int) $producto->stock);
        $this->assertCount(1, $producto->insumos);
    }

    public function test_un_plato_sin_receta_no_se_puede_crear(): void
    {
        $this->actingAs($this->admin)->post(route('admin.almacen.store'), [
            'codigo' => 'P3',
            'nombre' => 'Sin receta',
            'tipo' => 'plato',
            'categoria_id' => Categoria::first()->id,
        ])->assertSessionHasErrors('receta');

        $this->assertNull(Producto::where('codigo', 'P3')->first());
    }

    public function test_el_admin_edita_un_producto_con_stock_propio(): void
    {
        $this->actingAs($this->admin)->put(route('admin.almacen.update', $this->gaseosa->id), [
            'codigo' => 'B1',
            'nombre' => 'Gaseosa',
            'tipo' => 'directo',
            'categoria_id' => $this->gaseosa->categoria_id,
            'stock' => 40,
        ])->assertSessionHasNoErrors();

        $this->assertSame(40, (int) $this->gaseosa->fresh()->stock);
    }

    public function test_el_admin_gestiona_insumos_e_ingresos(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.insumos.store'), [
            'nombre' => 'Inca Kola 500ml',
            'categoria_insumo_id' => CategoriaInsumo::first()->id,
            'unidad' => 'unidad',
            'stock' => 12,
            'stock_minimo' => 3,
        ])->assertSessionHasNoErrors();

        $insumo = Insumo::where('nombre', 'Inca Kola 500ml')->firstOrFail();

        $this->post(route('admin.insumos.ingreso', $insumo->id), ['cantidad' => 2.5])->assertSessionHasNoErrors();
        $this->assertEqualsWithDelta(14.5, (float) $insumo->fresh()->stock, 0.001);

        // No se puede borrar un insumo usado en una receta
        $this->delete(route('admin.insumos.destroy', $this->arroz->id));
        $this->assertNotNull($this->arroz->fresh());
    }

    public function test_el_cliente_hace_un_prepedido_sin_iniciar_sesion_y_no_se_descuenta_stock(): void
    {
        $this->get(route('carta.index'))->assertOk()->assertSee('Arroz con pollo');

        $this->post(route('carta.store'), [
            'dni' => '87654321',
            'mesa' => ' 12 ',
            'items' => [$this->plato->id => 2],
        ])->assertSessionHasNoErrors()->assertRedirect(route('carta.index'));

        $pedido = Pedido::firstOrFail();
        $this->assertSame('cliente', $pedido->origen);
        $this->assertSame('12', $pedido->mesa);
        $this->assertSame(Pedido::PENDIENTE, $pedido->estado);
        $this->assertEqualsWithDelta(50, (float) $pedido->total, 0.001);
        $this->assertEqualsWithDelta(1, (float) $this->arroz->fresh()->stock, 0.001);
    }

    public function test_la_carta_oculta_lo_que_no_tiene_stock(): void
    {
        $this->arroz->update(['stock' => 0.1]);
        $this->gaseosa->update(['stock' => 0]);

        $this->get(route('carta.index'))->assertOk()
            ->assertDontSee('Arroz con pollo')
            ->assertDontSee('Gaseosa');
    }

    public function test_no_se_puede_pedir_mas_de_lo_que_alcanzan_los_insumos(): void
    {
        $this->post(route('carta.store'), [
            'dni' => '87654321',
            'mesa' => '4',
            'items' => [$this->plato->id => 4],
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, Pedido::count());
    }

    public function test_el_dni_debe_tener_8_digitos(): void
    {
        $this->post(route('carta.store'), [
            'dni' => '123',
            'mesa' => '4',
            'items' => [$this->plato->id => 1],
        ])->assertSessionHasErrors('dni');
    }

    public function test_el_cliente_debe_indicar_su_mesa(): void
    {
        $this->post(route('carta.store'), [
            'dni' => '87654321',
            'mesa' => '   ',
            'items' => [$this->plato->id => 1],
        ])->assertSessionHasErrors('mesa');

        $this->assertSame(0, Pedido::count());
    }

    public function test_la_carta_muestra_la_confirmacion_con_la_mesa(): void
    {
        $respuesta = $this->followingRedirects()->post(route('carta.store'), [
            'dni' => '87654321',
            'mesa' => '7',
            'items' => [$this->plato->id => 1, $this->gaseosa->id => 1],
        ]);

        $pedido = Pedido::firstOrFail();

        $respuesta->assertOk()
            ->assertSee('¡Pedido enviado!')
            ->assertSee($pedido->numero)
            ->assertSee('S/ 30.00')
            ->assertSee('87654321');

        // La confirmación sale una sola vez
        $this->get(route('carta.index'))->assertDontSee('¡Pedido enviado!');
    }

    public function test_la_carta_sigue_el_orden_de_la_carta_impresa_y_junta_personal_y_fuente(): void
    {
        $marinos = Categoria::create(['nombre' => 'Platos Marinos']);
        $crear = fn ($sub, $nombre, $precio) => Producto::create([
            'codigo' => Str::random(6), 'nombre' => $nombre, 'tipo' => 'directo', 'stock' => 9, 'precio_venta' => $precio,
            'categoria_id' => $marinos->id,
            'subcategoria_id' => Subcategoria::firstOrCreate(['nombre' => $sub, 'categoria_id' => $marinos->id])->id,
        ]);

        // Se crean en otro orden: la carta debe ponerlos como en el PDF
        $fuente = $crear('Fuentes de Camarones', 'Fuente de ceviche de camarón', 110);
        $personal = $crear('Camarones - Plato Personal', 'Ceviche de camarón', 55);
        $crear('Shots de Leche de Tigre', 'Leche de Tigre Clásica', 30);
        $sola = $crear('Fuentes de Camarones', 'Fuente de chupe de camarón', 100);

        $html = $this->get(route('carta.index'))->assertOk()
            ->assertSeeInOrder(['Shots de Leche de Tigre', 'Leche de Tigre Clásica', 'Nuestra Especialidad', 'Camarones', 'Ceviche de camarón', 'Personal', 'Fuente'])
            ->getContent();

        // Personal y fuente quedan en la misma fila, cada uno con su propio botón
        $this->assertMatchesRegularExpression('/class="fila doble".*?data-id="'.$personal->id.'".*?data-id="'.$fuente->id.'"/s', $html);
        // Una fuente sin plato personal sigue apareciendo sola
        $this->assertStringContainsString('data-id="'.$sola->id.'"', $html);
    }

    public function test_la_carta_agrupa_por_categoria_y_tiene_carrito(): void
    {
        $bebidas = Categoria::create(['nombre' => 'Bebidas frías']);
        $this->gaseosa->update(['categoria_id' => $bebidas->id]);

        $this->get(route('carta.index'))->assertOk()
            ->assertSeeInOrder(['Platos', 'Arroz con pollo', 'Bebidas frías', 'Gaseosa'])
            ->assertSee('data-chip=', false)
            ->assertSee('id="carrito"', false)
            ->assertSee('data-max="3"', false)
            ->assertSee('name="mesa"', false);
    }

    public function test_la_mesa_se_ve_en_las_listas_del_mozo_y_de_caja(): void
    {
        $this->post(route('carta.store'), [
            'dni' => '87654321',
            'mesa' => 'Terraza 9',
            'items' => [$this->gaseosa->id => 1],
        ])->assertSessionHasNoErrors();

        $this->actingAs($this->mozo)->get(route('mozo.pedidos.index'))->assertSee('Terraza 9');
        $this->actingAs($this->cajera)->get(route('cajera.pedidos.index'))->assertSee('Terraza 9');
    }

    public function test_la_cajera_registra_un_pedido_y_pasa_al_cobro(): void
    {
        $this->actingAs($this->cajera)->get(route('cajera.pedidos.create'))->assertOk()
            ->assertSee('Arroz con pollo')
            ->assertSee('Guardar y cobrar');

        $respuesta = $this->post(route('cajera.pedidos.store'), [
            'dni' => '44556677',
            'cliente_nombre' => 'Para llevar',
            'items' => [$this->plato->id => 1, $this->gaseosa->id => 1],
        ]);

        $pedido = Pedido::firstOrFail();
        $respuesta->assertSessionHasNoErrors()->assertRedirect(route('cajera.pedidos.show', $pedido->id));

        $this->assertSame('caja', $pedido->origen);
        $this->assertSame($this->cajera->id, $pedido->mozo_id);
        $this->assertNull($pedido->mesa);
        $this->assertEqualsWithDelta(30, (float) $pedido->total, 0.001);
        // Registrar no descuenta stock: eso pasa al cobrar
        $this->assertSame(5, (int) $this->gaseosa->fresh()->stock);

        $this->get(route('cajera.pedidos.show', $pedido->id))->assertOk()->assertSee($this->cajera->name);

        $this->post(route('cajera.pedidos.cobrar', $pedido->id), ['pagos' => [
            ['metodo' => 'efectivo', 'monto' => 30],
        ]])->assertSessionHasNoErrors();

        $this->assertSame(Pedido::PAGADO, $pedido->fresh()->estado);
        $this->assertSame(4, (int) $this->gaseosa->fresh()->stock);
    }

    public function test_la_cajera_no_registra_un_pedido_sin_dni_ni_productos(): void
    {
        $this->actingAs($this->cajera)->post(route('cajera.pedidos.store'), [
            'dni' => '',
            'items' => [$this->plato->id => 0],
        ])->assertSessionHasErrors('dni');

        $this->post(route('cajera.pedidos.store'), [
            'dni' => '44556677',
            'items' => [$this->plato->id => 0],
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, Pedido::count());
    }

    public function test_el_mozo_no_entra_al_registro_de_caja(): void
    {
        $this->actingAs($this->mozo)->get(route('cajera.pedidos.create'))->assertForbidden();
        $this->actingAs($this->mozo)->post(route('cajera.pedidos.store'), [
            'dni' => '44556677',
            'items' => [$this->gaseosa->id => 1],
        ])->assertForbidden();
    }

    public function test_la_cajera_cobra_con_dos_pagos_y_se_descuenta_el_stock(): void
    {
        $pedido = $this->pedidoPendiente();

        $this->actingAs($this->cajera)
            ->get(route('cajera.pedidos.index', ['dni' => '12345678']))
            ->assertOk()->assertSee($pedido->numero);

        $this->get(route('cajera.pedidos.show', $pedido->id))->assertOk()->assertSee('Arroz con pollo');

        $respuesta = $this->post(route('cajera.pedidos.cobrar', $pedido->id), ['pagos' => [
            ['metodo' => 'yape', 'monto' => 20, 'numero_operacion' => 'OP-998'],
            ['metodo' => 'efectivo', 'monto' => 15],
        ]]);

        $respuesta->assertSessionHasNoErrors();
        $recibo = $pedido->fresh('recibo')->recibo;
        $respuesta->assertRedirect(route('recibos.show', $recibo->id));

        $this->assertSame(Pedido::PAGADO, $pedido->fresh()->estado);
        $this->assertCount(2, $recibo->pagos);
        $this->assertEqualsWithDelta(0.7, (float) $this->arroz->fresh()->stock, 0.001);
        $this->assertEqualsWithDelta(9, (float) $this->pollo->fresh()->stock, 0.001);
        $this->assertSame(3, (int) $this->gaseosa->fresh()->stock);

        $this->get(route('recibos.show', $recibo->id))->assertOk()
            ->assertSee($recibo->numero)
            ->assertSee('12345678')
            ->assertSee('OP-998')
            ->assertSee('Yape')
            ->assertSee('Efectivo');

        $this->actingAs($this->admin)->get(route('admin.ventas.index'))->assertOk()->assertSee($recibo->numero);
        $this->get(route('recibos.show', $recibo->id))->assertOk();
    }

    public function test_los_pagos_deben_sumar_el_total(): void
    {
        $pedido = $this->pedidoPendiente();

        $this->actingAs($this->cajera)->post(route('cajera.pedidos.cobrar', $pedido->id), ['pagos' => [
            ['metodo' => 'efectivo', 'monto' => 10],
        ]])->assertSessionHasErrors('pagos');

        $this->assertSame(Pedido::PENDIENTE, $pedido->fresh()->estado);
        $this->assertEqualsWithDelta(1, (float) $this->arroz->fresh()->stock, 0.001);
    }

    public function test_el_numero_de_operacion_es_obligatorio_salvo_en_efectivo(): void
    {
        $pedido = $this->pedidoPendiente();

        $this->actingAs($this->cajera)->post(route('cajera.pedidos.cobrar', $pedido->id), ['pagos' => [
            ['metodo' => 'yape', 'monto' => 35],
        ]])->assertSessionHasErrors('pagos.0.numero_operacion');

        $this->assertSame(Pedido::PENDIENTE, $pedido->fresh()->estado);
    }

    public function test_no_se_permiten_mas_de_dos_pagos(): void
    {
        $pedido = $this->pedidoPendiente();

        $this->actingAs($this->cajera)->post(route('cajera.pedidos.cobrar', $pedido->id), ['pagos' => [
            ['metodo' => 'efectivo', 'monto' => 10],
            ['metodo' => 'efectivo', 'monto' => 10],
            ['metodo' => 'efectivo', 'monto' => 15],
        ]])->assertSessionHasErrors('pagos');
    }

    public function test_un_pedido_no_se_cobra_dos_veces(): void
    {
        $pedido = $this->pedidoPendiente();
        $pagos = ['pagos' => [['metodo' => 'efectivo', 'monto' => 35]]];

        $this->actingAs($this->cajera)->post(route('cajera.pedidos.cobrar', $pedido->id), $pagos)->assertSessionHasNoErrors();
        $this->post(route('cajera.pedidos.cobrar', $pedido->id), $pagos)->assertSessionHasErrors('pedido');

        $this->assertSame(3, (int) $this->gaseosa->fresh()->stock);
    }

    public function test_si_el_stock_ya_no_alcanza_el_cobro_no_deja_cambios(): void
    {
        $pedido = $this->pedidoPendiente();
        $this->pollo->update(['stock' => 0]);

        $this->actingAs($this->cajera)->post(route('cajera.pedidos.cobrar', $pedido->id), ['pagos' => [
            ['metodo' => 'efectivo', 'monto' => 35],
        ]])->assertSessionHasErrors('pedido');

        // La gaseosa se descuenta antes que los insumos: la transacción debe revertirla
        $this->assertSame(5, (int) $this->gaseosa->fresh()->stock);
        $this->assertSame(Pedido::PENDIENTE, $pedido->fresh()->estado);
        $this->assertNull($pedido->fresh('recibo')->recibo);
    }

    public function test_un_pedido_anulado_no_se_puede_cobrar(): void
    {
        $pedido = $this->pedidoPendiente();

        $this->actingAs($this->mozo)->post(route('mozo.pedidos.anular', $pedido->id))->assertSessionHasNoErrors();
        $this->assertSame(Pedido::ANULADO, $pedido->fresh()->estado);

        $this->actingAs($this->cajera)->post(route('cajera.pedidos.cobrar', $pedido->id), ['pagos' => [
            ['metodo' => 'efectivo', 'monto' => 35],
        ]])->assertSessionHasErrors('pedido');
    }

    public function test_cada_rol_solo_entra_a_su_area(): void
    {
        $this->actingAs($this->mozo)->get(route('cajera.pedidos.index'))->assertForbidden();
        $this->actingAs($this->mozo)->get(route('admin.insumos.index'))->assertForbidden();
        $this->actingAs($this->cajera)->get(route('mozo.pedidos.create'))->assertForbidden();
        $this->actingAs($this->cajera)->get(route('admin.ventas.index'))->assertForbidden();
    }

    public function test_el_mozo_no_puede_ver_recibos(): void
    {
        $pedido = $this->pedidoPendiente();

        $this->actingAs($this->cajera)->post(route('cajera.pedidos.cobrar', $pedido->id), ['pagos' => [
            ['metodo' => 'efectivo', 'monto' => 35],
        ]]);

        $this->actingAs($this->mozo)->get(route('recibos.show', $pedido->fresh('recibo')->recibo->id))->assertForbidden();
    }

    public function test_un_producto_con_pedidos_no_se_puede_eliminar(): void
    {
        $this->pedidoPendiente();

        $this->actingAs($this->admin)->delete(route('admin.almacen.destroy', $this->gaseosa->id))
            ->assertSessionHas('error');

        $this->assertNotNull($this->gaseosa->fresh());
    }

    public function test_todas_las_pantallas_cargan(): void
    {
        $this->pedidoPendiente();

        foreach (['admin.dashboard', 'admin.insumos.index', 'admin.insumos.create', 'admin.insumos.categorias.index', 'admin.almacen.index', 'admin.almacen.create', 'admin.ventas.index'] as $ruta) {
            $this->actingAs($this->admin)->get(route($ruta))->assertOk();
        }

        $this->get(route('admin.insumos.edit', $this->arroz->id))->assertOk();
        $this->get(route('admin.almacen.edit', $this->plato->id))->assertOk()->assertSee('Arroz');

        foreach (['mozo.dashboard', 'mozo.pedidos.index', 'mozo.pedidos.create'] as $ruta) {
            $this->actingAs($this->mozo)->get(route($ruta))->assertOk();
        }

        foreach (['cajera.dashboard', 'cajera.pedidos.index', 'cajera.pedidos.create', 'cajera.recibos.index'] as $ruta) {
            $this->actingAs($this->cajera)->get(route($ruta))->assertOk();
        }
    }
}
