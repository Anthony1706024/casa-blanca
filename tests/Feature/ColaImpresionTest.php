<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Impresion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Recibo;
use App\Models\Role;
use App\Models\User;
use App\Services\ColaImpresionService;
use App\Services\ComandaService;
use App\Services\TicketeraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class ColaImpresionTest extends TestCase
{
    use RefreshDatabase;

    private const CLAVE = 'clave-de-prueba-0123456789';

    private User $cajera;

    private Pedido $pedido;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.ticketera.modo' => 'cola', 'services.ticketera.clave' => self::CLAVE, 'services.ticketera.impresora' => 'No se usa en cola']);
        Process::fake();

        $this->cajera = User::factory()->create(['name' => 'María Flores', 'role_id' => Role::create(['nombre' => 'cajera'])->id]);
        Role::create(['nombre' => 'admin']);

        $categoria = Categoria::create(['nombre' => 'Carta']);
        $crear = fn ($codigo, $nombre, $precio, $cocina) => Producto::create([
            'codigo' => $codigo, 'nombre' => $nombre, 'tipo' => 'directo', 'cocina' => $cocina,
            'categoria_id' => $categoria->id, 'stock' => 20, 'precio_venta' => $precio,
        ]);
        $ceviche = $crear('M1', 'Ceviche mixto', 50, 'cocina_1');
        $cerveza = $crear('B1', 'Pilsen Callao', 12, 'bebidas');
        $cecina = $crear('C2', 'Cecina de cerdo', 45, 'cocina_2');

        $this->actingAs($this->cajera)->post(route('cajera.pedidos.store'), [
            'dni' => '74084122',
            'mesa' => '12',
            'items' => [$ceviche->id => 1, $cerveza->id => 2, $cecina->id => 1],
        ])->assertSessionHasNoErrors();

        $this->pedido = Pedido::firstOrFail();
    }

    private function agente(?string $clave = self::CLAVE): static
    {
        return $this->withHeaders($clave === null ? [] : ['Authorization' => 'Bearer '.$clave]);
    }

    private function encolar(int $cantidad): array
    {
        return collect(range(1, $cantidad))
            ->map(fn ($i) => app(ColaImpresionService::class)->encolar("bytes {$i}", Impresion::COMANDA, "Trabajo {$i}")->id)
            ->all();
    }

    public function test_al_cobrar_el_voucher_queda_en_la_cola_con_los_mismos_bytes(): void
    {
        $this->post(route('cajera.pedidos.cobrar', $this->pedido->id), ['pagos' => [['metodo' => 'efectivo', 'monto' => 119]]])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('impreso', 'Voucher enviado a la ticketera.');

        $recibo = Recibo::firstOrFail();
        $impresion = Impresion::sole();

        $this->assertSame(Impresion::VOUCHER, $impresion->tipo);
        $this->assertSame(Impresion::PENDIENTE, $impresion->estado);
        $this->assertSame($recibo->id, $impresion->recibo_id);
        $this->assertSame($this->pedido->id, $impresion->pedido_id);
        $this->assertSame("Voucher {$recibo->numero} – {$this->pedido->numero}", $impresion->descripcion);
        $this->assertSame(app(TicketeraService::class)->voucher($recibo->fresh()), $impresion->bytes);
        Process::assertNothingRan();

        // El recibo no abre el diálogo de impresión del navegador
        $this->get(route('recibos.show', $recibo->id))->assertSee('Imprimir en ticketera')->assertDontSee('window.print(); }, 300');
    }

    public function test_el_ticket_de_cocina_queda_en_la_cola_en_un_solo_trabajo(): void
    {
        $lote = app(ComandaService::class)->lote($this->pedido->fresh());
        $esperado = app(TicketeraService::class)->comanda($this->pedido->fresh(), $lote);

        $this->post(route('cajera.pedidos.comanda', $this->pedido->id))
            ->assertRedirect(route('cajera.pedidos.comanda.ver', $this->pedido->id))
            ->assertSessionHas('success', 'Ticket enviado a la ticketera: Cocina 1, Bebidas y tragos, Cocina 2.');

        $impresion = Impresion::sole();
        $this->assertSame(Impresion::COMANDA, $impresion->tipo);
        $this->assertSame("Comanda {$this->pedido->numero} – Cocina 1, Bebidas y tragos, Cocina 2", $impresion->descripcion);
        $this->assertSame($esperado, $impresion->bytes);
        $this->assertSame(3, substr_count($impresion->bytes, "\x1D\x56\x42\x00"), 'Tres tickets, cada uno con su corte');
        $this->assertTrue($this->pedido->items()->get()->every(fn ($item) => $item->cantidad_enviada === $item->cantidad));
        Process::assertNothingRan();

        // La ventana del ticket no lanza el diálogo de impresión del navegador
        $this->get(route('cajera.pedidos.comanda.ver', $this->pedido->id))->assertOk()->assertDontSee('window.print(); }, 300');

        // Reimprimir también va a la cola
        $this->post(route('cajera.pedidos.comanda', $this->pedido->id), ['todo' => 1]);
        $this->assertStringEndsWith('(reimpresión)', Impresion::latest('id')->first()->descripcion);
    }

    public function test_sin_clave_la_cola_no_esta_configurada_y_se_usa_el_navegador(): void
    {
        config(['services.ticketera.clave' => null]);

        $this->assertFalse(app(TicketeraService::class)->configurada());
        $this->post(route('cajera.pedidos.comanda', $this->pedido->id));

        $this->assertSame(0, Impresion::count());
        Process::assertNothingRan();
    }

    public function test_el_agente_necesita_la_clave_correcta(): void
    {
        $this->encolar(1);
        auth()->logout();

        $this->agente(null)->getJson(route('ticketera.pendientes'))->assertUnauthorized();
        $this->agente('clave-equivocada')->getJson(route('ticketera.pendientes'))->assertUnauthorized();
        $this->agente(null)->postJson(route('ticketera.impresa', 1))->assertUnauthorized();

        config(['services.ticketera.clave' => '']);
        $this->agente('')->getJson(route('ticketera.pendientes'))->assertForbidden();

        $this->assertSame(Impresion::PENDIENTE, Impresion::first()->estado);
    }

    public function test_el_agente_recibe_los_pendientes_en_orden_y_una_sola_vez(): void
    {
        [$primero, $segundo, $tercero] = $this->encolar(3);
        auth()->logout();

        $respuesta = $this->agente()->getJson(route('ticketera.pendientes', ['limite' => 2]))->assertOk();
        $this->assertSame([$primero, $segundo], $respuesta->json('*.id'));
        $this->assertSame(base64_encode('bytes 1'), $respuesta->json('0.datos'));
        $this->assertSame('Trabajo 1', $respuesta->json('0.descripcion'));
        $this->assertSame(Impresion::ENVIANDO, Impresion::find($primero)->estado);

        // Los ya entregados no se repiten
        $this->assertSame([$tercero], $this->agente()->getJson(route('ticketera.pendientes'))->json('*.id'));
        $this->assertSame([], $this->agente()->getJson(route('ticketera.pendientes'))->json());

        // Tras 2 minutos en "enviando" sin respuesta, se vuelven a ofrecer
        $this->travel(3)->minutes();
        $this->assertSame([$primero, $segundo, $tercero], $this->agente()->getJson(route('ticketera.pendientes'))->json('*.id'));
    }

    public function test_impresa_error_y_reintento(): void
    {
        [$id] = $this->encolar(1);
        auth()->logout();
        $this->agente()->getJson(route('ticketera.pendientes'));

        // Dos errores: vuelve a la cola
        foreach ([1, 2] as $intento) {
            $this->agente()->postJson(route('ticketera.error', $id), ['mensaje' => 'Sin papel'])->assertOk()->assertJson(['estado' => Impresion::PENDIENTE]);
            $this->assertSame($intento, Impresion::find($id)->intentos);
        }

        // Al tercero queda en error y ya no se ofrece
        $this->agente()->postJson(route('ticketera.error', $id), ['mensaje' => 'Sin papel'])->assertJson(['estado' => Impresion::ERROR]);
        $this->assertSame('Sin papel', Impresion::find($id)->error);
        $this->assertSame([], $this->agente()->getJson(route('ticketera.pendientes'))->json());

        // La cajera la ve y la reintenta
        $this->actingAs($this->cajera)->get(route('impresiones.index'))->assertOk()
            ->assertSee('Trabajo 1')->assertSee('Sin papel')->assertSee('Reintentar');
        $this->post(route('impresiones.reintentar', $id))->assertSessionHas('success');
        $this->assertSame(Impresion::PENDIENTE, Impresion::find($id)->estado);
        $this->assertSame(0, Impresion::find($id)->intentos);

        // Ahora sí sale
        auth()->logout();
        $this->assertSame([$id], $this->agente()->getJson(route('ticketera.pendientes'))->json('*.id'));
        $this->agente()->postJson(route('ticketera.impresa', $id))->assertOk();
        $impresion = Impresion::find($id);
        $this->assertSame(Impresion::IMPRESA, $impresion->estado);
        $this->assertNotNull($impresion->impresa_en);
        $this->assertNull($impresion->error);
    }

    public function test_solo_se_reintentan_las_que_tienen_error(): void
    {
        [$id] = $this->encolar(1);

        $this->post(route('impresiones.reintentar', $id))->assertSessionHas('error');
        $this->agente()->postJson(route('ticketera.impresa', 999))->assertNotFound();
    }

    public function test_la_pantalla_avisa_si_la_ticketera_no_recibe(): void
    {
        $this->encolar(1);
        $aviso = 'La ticketera no está recibiendo';

        $this->get(route('impresiones.index'))->assertOk()->assertDontSee($aviso);

        $this->travel(2)->minutes();
        $this->get(route('impresiones.index'))->assertSee($aviso);
    }

    public function test_caja_y_admin_ven_impresiones_pero_el_mozo_no(): void
    {
        $admin = User::factory()->create(['role_id' => Role::where('nombre', 'admin')->value('id')]);
        $mozo = User::factory()->create(['role_id' => Role::create(['nombre' => 'mozo'])->id]);

        $this->get(route('cajera.dashboard'))->assertSee(route('impresiones.index'));
        $this->actingAs($admin)->get(route('impresiones.index'))->assertOk()->assertSee('Ver Ventas');
        $this->actingAs($mozo)->get(route('impresiones.index'))->assertForbidden();
    }

    public function test_el_agente_no_crea_sesiones(): void
    {
        $this->encolar(1);
        auth()->logout();

        $this->agente()->getJson(route('ticketera.pendientes'))->assertOk()->assertCookieMissing(config('session.cookie'));
    }

    public function test_en_modo_directo_se_imprime_como_antes(): void
    {
        config(['services.ticketera.modo' => 'directo', 'services.ticketera.impresora' => 'Ticketera de prueba']);

        $this->post(route('cajera.pedidos.comanda', $this->pedido->id))
            ->assertSessionHas('success', 'Tickets enviados a cocina: Cocina 1, Bebidas y tragos, Cocina 2.');

        Process::assertRan(fn ($proceso) => in_array('Ticketera de prueba', $proceso->command, true));
        $this->assertSame(0, Impresion::count());
    }
}
