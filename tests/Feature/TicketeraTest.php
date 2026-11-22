<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Recibo;
use App\Models\Role;
use App\Models\User;
use App\Services\TicketeraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class TicketeraTest extends TestCase
{
    use RefreshDatabase;

    private User $cajera;

    private Recibo $recibo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cajera = User::factory()->create(['name' => 'María Flores', 'role_id' => Role::create(['nombre' => 'cajera'])->id]);
        $mozo = User::factory()->create(['name' => 'Juan Pérez', 'role_id' => Role::create(['nombre' => 'mozo'])->id]);

        $categoria = Categoria::create(['nombre' => 'Platos']);
        $producto = Producto::create(['codigo' => 'P1', 'nombre' => 'Picante de camarón', 'tipo' => 'directo', 'categoria_id' => $categoria->id, 'stock' => 5, 'precio_venta' => 55]);

        $pedido = Pedido::create(['dni' => '12345678', 'mesa' => '5', 'origen' => 'mozo', 'mozo_id' => $mozo->id, 'estado' => Pedido::PAGADO, 'total' => 110]);
        $pedido->items()->create(['producto_id' => $producto->id, 'producto_nombre' => 'Picante de camarón', 'cantidad' => 2, 'precio_unitario' => 55, 'subtotal' => 110]);

        $this->recibo = Recibo::create(['pedido_id' => $pedido->id, 'cajero_id' => $this->cajera->id, 'total' => 110]);
        $this->recibo->pagos()->create(['metodo' => 'yape', 'monto' => 110, 'numero_operacion' => 'OP-77']);
    }

    public function test_el_voucher_lleva_los_datos_del_cobro(): void
    {
        $voucher = mb_convert_encoding(app(TicketeraService::class)->voucher($this->recibo), 'UTF-8', 'CP850');

        foreach (['RUC: 10181327401', 'COMPROBANTE DE CONSUMO', 'Nro. 0000001', 'Mesa: 05', 'DNI: 12345678',
            'Atendido por: Juan', 'Picante de camaron', '110.00', 'S/ 55.00 c/u', 'TOTAL A PAGAR', 'Yape', 'OP-77', 'GRACIAS POR SU PREFERENCIA!!', 'Los mejores momentos se comparten en familia'] as $texto) {
            $this->assertStringContainsString($texto, $voucher);
        }
        $this->assertStringEndsWith("\x1D\x56\x42\x00", $voucher, 'El voucher debe terminar cortando el papel');
        $this->assertStringNotContainsString('No válido', $voucher);
        // Sin estado ni negrita (ESC E 1)
        $this->assertStringNotContainsString('Estado', $voucher);
        $this->assertStringNotContainsString("\x1B\x45\x01", app(TicketeraService::class)->voucher($this->recibo));
        $this->actingAs($this->cajera)->get(route('recibos.show', $this->recibo->id))
            ->assertDontSee('Estado:')
            ->assertSee('.ticket, .ticket * { font-weight: normal !important; }', false);
    }

    public function test_el_voucher_lleva_arriba_el_camaron_con_el_nombre_en_letra_de_adorno(): void
    {
        $logo = file_get_contents(resource_path('ticketera/logo.bin'));
        $this->assertStringStartsWith("\x1D\x76\x30\x00", $logo, 'El encabezado es una imagen GS v 0');

        $bytes = app(TicketeraService::class)->voucher($this->recibo);
        $this->assertSame(1, substr_count($bytes, $logo));
        // El nombre ya va dentro de la imagen: no se repite en texto antes del comprobante
        $this->assertLessThan(strpos($bytes, 'RUC:'), strpos($bytes, $logo));
        $this->assertStringNotContainsString('CASA BLANCA', substr($bytes, 0, strpos($bytes, 'COMPROBANTE')));

        $this->actingAs($this->cajera)->get(route('recibos.show', $this->recibo->id))
            ->assertSee('img/camaron.webp')
            ->assertSee('Restaurante Campestre')
            ->assertSee('Casa Blanca')
            ->assertDontSee('No válido como comprobante de pago');
    }

    public function test_sin_ticketera_configurada_no_se_intenta_imprimir(): void
    {
        Process::fake();
        config(['services.ticketera.impresora' => null]);

        $this->assertFalse(app(TicketeraService::class)->imprimirVoucher($this->recibo));
        Process::assertNothingRan();

        $this->actingAs($this->cajera)->get(route('recibos.show', $this->recibo->id))
            ->assertOk()->assertSee('Imprimir voucher')->assertDontSee('Imprimir en ticketera');
    }

    public function test_con_ticketera_se_envia_el_voucher_a_la_impresora(): void
    {
        Process::fake();
        config(['services.ticketera.impresora' => 'Ticketera de prueba']);

        $this->actingAs($this->cajera)->post(route('recibos.imprimir', $this->recibo->id))
            ->assertSessionHas('impreso');

        Process::assertRan(fn ($proceso) => in_array('Ticketera de prueba', $proceso->command, true));
    }

    public function test_si_la_ticketera_falla_se_avisa(): void
    {
        Process::fake(['*' => Process::result(exitCode: 1, errorOutput: 'sin papel')]);
        config(['services.ticketera.impresora' => 'Ticketera de prueba']);

        $this->actingAs($this->cajera)->post(route('recibos.imprimir', $this->recibo->id))
            ->assertSessionHas('error');
    }
}
