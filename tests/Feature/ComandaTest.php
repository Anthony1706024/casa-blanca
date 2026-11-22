<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Role;
use App\Models\User;
use App\Services\ComandaService;
use App\Services\TicketeraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class ComandaTest extends TestCase
{
    use RefreshDatabase;

    private const CORTAR = "\x1D\x56\x42\x00";

    private User $cajera;

    private User $mozo;

    private User $admin;

    private Producto $ceviche;

    private Producto $cerveza;

    private Producto $cecina;

    private Pedido $pedido;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cajera = User::factory()->create(['name' => 'María Flores', 'role_id' => Role::create(['nombre' => 'cajera'])->id]);
        $this->mozo = User::factory()->create(['name' => 'Juan Pérez', 'role_id' => Role::create(['nombre' => 'mozo'])->id]);
        $this->admin = User::factory()->create(['role_id' => Role::create(['nombre' => 'admin'])->id]);

        $categoria = Categoria::create(['nombre' => 'Carta']);
        $crear = fn ($codigo, $nombre, $precio, $cocina) => Producto::create([
            'codigo' => $codigo, 'nombre' => $nombre, 'tipo' => 'directo', 'cocina' => $cocina,
            'categoria_id' => $categoria->id, 'stock' => 10, 'precio_venta' => $precio,
        ]);

        $this->ceviche = $crear('C1', 'Ceviche mixto', 50, 'cocina_1');
        $this->cerveza = $crear('B1', 'Pilsen Callao', 12, 'bebidas');
        $this->cecina = $crear('C2', 'Cecina de cerdo', 45, 'cocina_2');

        $this->actingAs($this->cajera)->post(route('cajera.pedidos.store'), [
            'dni' => '74084122',
            'cliente_nombre' => 'Anthony Palma',
            'mesa' => '30',
            'observacion' => 'el pato sin aji',
            'items' => [$this->ceviche->id => 1, $this->cerveza->id => 2, $this->cecina->id => 1],
        ])->assertSessionHasNoErrors();

        $this->pedido = Pedido::firstOrFail();
    }

    private function texto(string $bytes): string
    {
        return mb_convert_encoding($bytes, 'UTF-8', 'CP850');
    }

    public function test_un_producto_nuevo_va_a_la_cocina_2_por_defecto(): void
    {
        $producto = Producto::create(['codigo' => 'X1', 'nombre' => 'Nuevo', 'tipo' => 'directo', 'stock' => 1, 'precio_venta' => 5, 'categoria_id' => $this->ceviche->categoria_id]);

        $this->assertSame('cocina_2', $producto->fresh()->cocina);
        $this->assertSame('Cocina 2', $producto->fresh()->cocina_nombre);
    }

    public function test_el_pedido_se_divide_en_tres_tickets_en_orden(): void
    {
        $lote = app(ComandaService::class)->lote($this->pedido);
        $this->assertSame(['cocina_1', 'bebidas', 'cocina_2'], array_keys($lote['tickets']));

        $bytes = app(TicketeraService::class)->comanda($this->pedido, $lote);
        $texto = $this->texto($bytes);

        $this->assertSame(3, substr_count($bytes, self::CORTAR), 'Cada ticket termina con su corte');
        $this->assertStringEndsWith(self::CORTAR, $bytes);
        $this->assertTrue(strpos($texto, 'COCINA 1') < strpos($texto, 'BEBIDAS Y TRAGOS'));
        $this->assertTrue(strpos($texto, 'BEBIDAS Y TRAGOS') < strpos($texto, 'COCINA 2'));

        // Cada producto sale solo en el ticket de su cocina
        [$primero, $segundo, $tercero] = explode(self::CORTAR, $bytes);
        $this->assertStringContainsString('Ceviche mixto', $this->texto($primero));
        $this->assertStringNotContainsString('Pilsen', $this->texto($primero));
        $this->assertStringContainsString(' 2 x Pilsen Callao', $this->texto($segundo));
        $this->assertStringContainsString('Cecina de cerdo', $this->texto($tercero));

        foreach (['MESA 30', 'PD-000001', 'Cliente: Anthony Palma', 'Atiende: Maria', 'el pato sin aji', 'Ticket 1 de 3'] as $dato) {
            $this->assertStringContainsString($dato, $texto);
        }
    }

    public function test_una_cocina_sin_productos_no_imprime_ticket(): void
    {
        $this->pedido->items()->where('producto_id', $this->cerveza->id)->delete();

        $bytes = app(TicketeraService::class)->comanda($this->pedido->fresh(), app(ComandaService::class)->lote($this->pedido->fresh()));

        $this->assertSame(2, substr_count($bytes, self::CORTAR));
        $this->assertStringNotContainsString('BEBIDAS Y TRAGOS', $this->texto($bytes));
        $this->assertStringContainsString('Ticket 2 de 2', $this->texto($bytes));
    }

    public function test_con_ticketera_se_imprimen_y_quedan_enviados(): void
    {
        Process::fake();
        config(['services.ticketera.impresora' => 'Ticketera de prueba']);

        $this->actingAs($this->cajera)->get(route('cajera.pedidos.show', $this->pedido->id))
            ->assertSee('Imprimir ticket cocina')->assertSee('Agregar adicionales')->assertSee('Por enviar a cocina');

        // Termina en la ventana del ticket, no en la pestaña del pedido
        $this->post(route('cajera.pedidos.comanda', $this->pedido->id))
            ->assertRedirect(route('cajera.pedidos.comanda.ver', $this->pedido->id))
            ->assertSessionHas('success', 'Tickets enviados a cocina: Cocina 1, Bebidas y tragos, Cocina 2.');

        Process::assertRanTimes(fn ($proceso) => in_array('Ticketera de prueba', $proceso->command, true), 1);
        $this->assertTrue($this->pedido->items()->get()->every(fn ($item) => $item->cantidad_enviada === $item->cantidad));

        // Sin nada nuevo no se repite; se ofrece reimprimir
        $this->post(route('cajera.pedidos.comanda', $this->pedido->id))
            ->assertRedirect(route('cajera.pedidos.comanda.ver', $this->pedido->id))->assertSessionHas('error');
        $this->get(route('cajera.pedidos.show', $this->pedido->id))->assertSee('fa-redo mr-1"></i> Ticket', false)->assertSee('En cocina');
        $this->get(route('cajera.pedidos.comanda.ver', $this->pedido->id))->assertSee('Reimprimir')->assertSee('(REIMPRESIÓN)');

        $this->post(route('cajera.pedidos.comanda', $this->pedido->id), ['todo' => 1])->assertSessionHas('success');
        Process::assertRanTimes(fn ($proceso) => true, 2);
    }

    public function test_los_tickets_de_cocina_no_llevan_logo(): void
    {
        $bytes = app(TicketeraService::class)->comanda($this->pedido, app(ComandaService::class)->lote($this->pedido));
        $this->assertStringNotContainsString("\x1D\x76\x30\x00", $bytes);
    }

    public function test_si_la_ticketera_falla_no_se_marca_como_enviado(): void
    {
        Process::fake(['*' => Process::result(exitCode: 1, errorOutput: 'sin papel')]);
        config(['services.ticketera.impresora' => 'Ticketera de prueba']);

        $this->actingAs($this->cajera)->post(route('cajera.pedidos.comanda', $this->pedido->id))->assertSessionHas('error');

        $this->assertSame(0, (int) $this->pedido->items()->sum('cantidad_enviada'));
    }

    public function test_sin_ticketera_se_imprime_desde_el_navegador(): void
    {
        Process::fake();
        config(['services.ticketera.impresora' => null]);

        $this->actingAs($this->cajera)->post(route('cajera.pedidos.comanda', $this->pedido->id))
            ->assertRedirect(route('cajera.pedidos.comanda.ver', $this->pedido->id));

        Process::assertNothingRan();

        $this->get(route('cajera.pedidos.comanda.ver', $this->pedido->id))->assertOk()
            ->assertSeeInOrder(['COCINA 1', 'Ceviche mixto', 'BEBIDAS Y TRAGOS', 'Pilsen Callao', 'COCINA 2', 'Cecina de cerdo'])
            ->assertSee('MESA 30')
            ->assertDontSee('img/logo-claro.webp')
            ->assertSee('Imprimir 3 tickets');
    }

    public function test_los_adicionales_se_suman_al_pedido_y_solo_ellos_van_a_cocina(): void
    {
        $servicio = app(ComandaService::class);
        $servicio->marcarEnviado($servicio->lote($this->pedido));

        $this->actingAs($this->cajera)->get(route('cajera.pedidos.adicionales', $this->pedido->id))
            ->assertOk()->assertSee('Mesa 30')->assertSee('Agregar al pedido');

        $this->post(route('cajera.pedidos.adicionales.store', $this->pedido->id), [
            'items' => [$this->cerveza->id => 1, $this->cecina->id => 0],
        ])->assertSessionHasNoErrors()->assertRedirect(route('cajera.pedidos.show', $this->pedido->id));

        $pedido = $this->pedido->fresh('items');
        // Misma línea, misma mesa: 2 + 1 cervezas, sin una línea nueva
        $this->assertCount(3, $pedido->items);
        $this->assertSame(3, $pedido->items->firstWhere('producto_id', $this->cerveza->id)->cantidad);
        $this->assertEqualsWithDelta(50 + 36 + 45, (float) $pedido->total, 0.001);
        // Agregar no descuenta stock: eso pasa al cobrar
        $this->assertSame(10, (int) $this->cerveza->fresh()->stock);

        $lote = $servicio->lote($pedido);
        $this->assertSame(['bebidas' => [['cantidad' => 1, 'nombre' => 'Pilsen Callao']]], $lote['tickets']);
        $this->assertTrue($lote['adicional']);
        $this->assertStringContainsString('*** ADICIONAL ***', $this->texto(app(TicketeraService::class)->comanda($pedido, $lote)));

        // Al cobrar se cobra todo, adicionales incluidos
        $this->post(route('cajera.pedidos.cobrar', $pedido->id), ['pagos' => [['metodo' => 'efectivo', 'monto' => 131]]])
            ->assertSessionHasNoErrors();
        $this->assertSame(7, (int) $this->cerveza->fresh()->stock);
    }

    public function test_un_adicional_con_otro_precio_va_en_su_propia_linea(): void
    {
        $this->cerveza->update(['precio_venta' => 13]);

        $this->actingAs($this->cajera)->post(route('cajera.pedidos.adicionales.store', $this->pedido->id), [
            'items' => [$this->cerveza->id => 1],
        ])->assertSessionHasNoErrors();

        $lineas = $this->pedido->items()->where('producto_id', $this->cerveza->id)->orderBy('id')->get();
        $this->assertSame([2, 1], $lineas->pluck('cantidad')->all());
        $this->assertEqualsWithDelta(50 + 24 + 45 + 13, (float) $this->pedido->fresh()->total, 0.001);
    }

    public function test_los_adicionales_respetan_el_stock_de_todo_el_pedido(): void
    {
        // Ya pidió 2 de 10: 9 más no alcanzan
        $this->actingAs($this->cajera)->post(route('cajera.pedidos.adicionales.store', $this->pedido->id), [
            'items' => [$this->cerveza->id => 9],
        ])->assertSessionHasErrors('items');

        $this->assertEqualsWithDelta(50 + 24 + 45, (float) $this->pedido->fresh()->total, 0.001);
    }

    public function test_no_se_agregan_adicionales_a_un_pedido_cobrado_o_anulado(): void
    {
        $this->pedido->update(['estado' => Pedido::ANULADO]);

        $this->actingAs($this->cajera)->post(route('cajera.pedidos.adicionales.store', $this->pedido->id), [
            'items' => [$this->cerveza->id => 1],
        ])->assertSessionHasErrors('pedido');

        $this->post(route('cajera.pedidos.comanda', $this->pedido->id))->assertSessionHas('error');
    }

    public function test_la_cajera_quita_una_unidad_o_la_linea_completa(): void
    {
        $cerveza = $this->pedido->items()->where('producto_id', $this->cerveza->id)->first();
        $ceviche = $this->pedido->items()->where('producto_id', $this->ceviche->id)->first();

        $this->actingAs($this->cajera)->get(route('cajera.pedidos.show', $this->pedido->id))
            ->assertSee(route('cajera.pedidos.items.quitar', [$this->pedido->id, $cerveza->id]));

        $this->post(route('cajera.pedidos.items.quitar', [$this->pedido->id, $cerveza->id]), ['cantidad' => 1])
            ->assertSessionHasNoErrors()->assertRedirect(route('cajera.pedidos.show', $this->pedido->id));
        $this->assertSame(1, $cerveza->fresh()->cantidad);
        $this->assertEqualsWithDelta(12, (float) $cerveza->fresh()->subtotal, 0.001);

        $this->post(route('cajera.pedidos.items.quitar', [$this->pedido->id, $ceviche->id]))->assertSessionHasNoErrors();
        $this->assertNull($ceviche->fresh());
        $this->assertEqualsWithDelta(12 + 45, (float) $this->pedido->fresh()->total, 0.001);

        // El cobro usa el total nuevo
        $this->post(route('cajera.pedidos.cobrar', $this->pedido->id), ['pagos' => [['metodo' => 'efectivo', 'monto' => 57]]])
            ->assertSessionHasNoErrors();
    }

    public function test_quitar_algo_que_ya_salio_a_cocina_avisa_y_no_se_reimprime(): void
    {
        $servicio = app(ComandaService::class);
        $servicio->marcarEnviado($servicio->lote($this->pedido));
        $cerveza = $this->pedido->items()->where('producto_id', $this->cerveza->id)->first();

        $this->actingAs($this->cajera)->post(route('cajera.pedidos.items.quitar', [$this->pedido->id, $cerveza->id]), ['cantidad' => 1])
            ->assertSessionHas('success', fn ($mensaje) => str_contains($mensaje, 'Ya estaba en cocina'));

        $this->assertSame(1, $cerveza->fresh()->cantidad_enviada);
        $this->assertSame([], $servicio->lote($this->pedido->fresh())['tickets']);
    }

    public function test_no_se_quita_el_unico_producto_ni_de_otro_pedido(): void
    {
        $this->pedido->items()->where('producto_id', '!=', $this->ceviche->id)->delete();
        $ceviche = $this->pedido->items()->first();

        $this->actingAs($this->cajera)->post(route('cajera.pedidos.items.quitar', [$this->pedido->id, $ceviche->id]))
            ->assertSessionHasErrors('pedido');
        $this->assertNotNull($ceviche->fresh());

        $otro = Pedido::create(['dni' => '11111111', 'origen' => 'caja', 'estado' => Pedido::PENDIENTE, 'total' => 0]);
        $this->post(route('cajera.pedidos.items.quitar', [$otro->id, $ceviche->id]))->assertNotFound();

        $this->pedido->update(['estado' => Pedido::PAGADO]);
        $this->post(route('cajera.pedidos.items.quitar', [$this->pedido->id, $ceviche->id]), ['cantidad' => 1])
            ->assertSessionHasErrors('pedido');
    }

    public function test_el_mozo_no_usa_las_pantallas_de_caja(): void
    {
        $this->actingAs($this->mozo)->post(route('cajera.pedidos.comanda', $this->pedido->id))->assertForbidden();
        $this->actingAs($this->mozo)->get(route('cajera.pedidos.adicionales', $this->pedido->id))->assertForbidden();
    }

    public function test_el_admin_cambia_la_cocina_de_un_producto(): void
    {
        $this->actingAs($this->admin)->get(route('admin.precios.index', ['cocina' => 'bebidas']))
            ->assertOk()->assertSee('Pilsen Callao')->assertDontSee('Cecina de cerdo');

        $this->put(route('admin.precios.actualizar'), [
            'precios' => [$this->cecina->id => 45],
            'cocinas' => [$this->cecina->id => 'cocina_1'],
        ])->assertSessionHasNoErrors();
        $this->assertSame('cocina_1', $this->cecina->fresh()->cocina);

        $this->put(route('admin.precios.actualizar'), [
            'precios' => [$this->cecina->id => 45],
            'cocinas' => [$this->cecina->id => 'cocina_9'],
        ])->assertSessionHasErrors('cocinas.'.$this->cecina->id);

        $this->get(route('admin.almacen.edit', $this->cecina->id))->assertOk()->assertSee('Se prepara en')->assertSee('Bebidas y tragos');

        $this->put(route('admin.almacen.update', $this->cecina->id), [
            'codigo' => 'C2', 'nombre' => 'Cecina de cerdo', 'tipo' => 'directo', 'stock' => 10,
            'categoria_id' => $this->cecina->categoria_id, 'precio_venta' => 45, 'cocina' => 'cocina_2',
        ])->assertSessionHasNoErrors();
        $this->assertSame('cocina_2', $this->cecina->fresh()->cocina);
    }
}
