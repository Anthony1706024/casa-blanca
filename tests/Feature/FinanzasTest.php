<?php

namespace Tests\Feature;

use App\Models\CategoriaEgreso;
use App\Models\Egreso;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Recibo;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanzasTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-22 15:00:00');
        $this->admin = User::factory()->create(['role_id' => Role::create(['nombre' => 'admin'])->id]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function venta(string $cuando, array $pagos): Recibo
    {
        $total = array_sum($pagos);
        $pedido = Pedido::create(['mesa' => '1', 'origen' => 'caja', 'estado' => Pedido::PAGADO, 'total' => $total]);
        $recibo = Recibo::create(['pedido_id' => $pedido->id, 'cajero_id' => $this->admin->id, 'total' => $total]);
        $recibo->forceFill(['created_at' => $cuando])->save();
        foreach ($pagos as $metodo => $monto) {
            Pago::create(['recibo_id' => $recibo->id, 'metodo' => $metodo, 'monto' => $monto]);
        }

        return $recibo;
    }

    private function egreso(string $fecha, string $categoria, float $monto): Egreso
    {
        return Egreso::create([
            'fecha' => $fecha, 'monto' => $monto, 'descripcion' => 'Prueba',
            'categoria_egreso_id' => CategoriaEgreso::where('nombre', $categoria)->value('id'),
        ]);
    }

    public function test_vienen_los_tipos_de_egreso_pedidos(): void
    {
        foreach (['Compras de mercado', 'Compras variadas', 'Backus (cerveza)', 'Coca Cola (gaseosas)', 'Pagos al personal', 'Electrodomésticos',
            'Arreglos del local', 'Piscina (cloro, pastillas de cloro)', 'Obras', 'Trámites', 'Agua', 'Ferretería', 'Gas', 'Otros'] as $nombre) {
            $this->assertTrue(CategoriaEgreso::where('nombre', $nombre)->exists(), $nombre);
        }
    }

    public function test_ingresos_del_dia_por_forma_de_pago_con_egresos_y_balance(): void
    {
        $this->venta('2026-11-22 13:10:00', ['efectivo' => 50]);
        $this->venta('2026-11-22 14:00:00', ['yape' => 30, 'efectivo' => 20]);
        $this->venta('2026-11-21 20:00:00', ['plin' => 999]);   // otro día
        $this->egreso('2026-11-22', 'Gas', 40);
        $this->egreso('2026-11-20', 'Gas', 500);                 // otro día

        $respuesta = $this->actingAs($this->admin)->get(route('admin.ingresos.index'))->assertOk();

        $resumen = $respuesta->viewData('resumen');
        $this->assertEquals(100, $resumen['ingresos']);
        $this->assertSame(2, $resumen['recibos']);
        $this->assertEquals(70, $resumen['por_metodo']['efectivo']);
        $this->assertEquals(30, $resumen['por_metodo']['yape']);
        $this->assertEquals(0, $resumen['por_metodo']['plin']);
        $this->assertEquals(40, $resumen['egresos']);
        $this->assertEquals(60, $resumen['balance']);
        $respuesta->assertSee('S/ 100.00')->assertSee('S/ 60.00');
    }

    public function test_semana_mes_y_anio(): void
    {
        $this->venta('2026-11-16 12:00:00', ['efectivo' => 10]);   // lunes de esta semana
        $this->venta('2026-11-15 12:00:00', ['efectivo' => 20]);   // domingo anterior
        $this->venta('2026-11-01 12:00:00', ['efectivo' => 40]);
        $this->venta('2026-03-05 12:00:00', ['tarjeta' => 80]);
        $this->venta('2025-12-31 23:00:00', ['efectivo' => 1000]); // otro año
        $this->egreso('2026-03-01', 'Obras', 30);

        $ver = fn (array $p) => $this->actingAs($this->admin)->get(route('admin.ingresos.index', $p))->viewData('resumen');

        $this->assertEquals(10, $ver(['periodo' => 'semana', 'fecha' => '2026-11-22'])['ingresos']);
        $this->assertEquals(70, $ver(['periodo' => 'mes', 'fecha' => '2026-11-10'])['ingresos']);
        $anio = $ver(['periodo' => 'anio', 'fecha' => '2026-06-01']);
        $this->assertEquals(150, $anio['ingresos']);
        $this->assertEquals(120, $anio['balance']);
        $this->assertEquals(1080, $ver(['periodo' => 'rango', 'desde' => '2025-12-31', 'hasta' => '2026-03-31'])['ingresos']);

        // El año se agrupa por mes: marzo y noviembre
        $filas = $this->actingAs($this->admin)->get(route('admin.ingresos.index', ['periodo' => 'anio', 'fecha' => '2026-06-01']))->viewData('filas');
        $this->assertSame(['2026-11', '2026-03'], array_column($filas, 'clave'));
        $this->assertEquals(-30 + 80, $filas[1]['balance']);
    }

    public function test_el_admin_registra_edita_y_borra_egresos(): void
    {
        $gas = CategoriaEgreso::where('nombre', 'Gas')->first();

        $this->actingAs($this->admin)->post(route('admin.egresos.store'), [
            'fecha' => '2026-11-22', 'categoria_egreso_id' => $gas->id, 'descripcion' => '2 balones', 'monto' => '96.50', 'metodo' => 'efectivo',
        ])->assertRedirect()->assertSessionHas('success');

        $egreso = Egreso::sole();
        $this->assertSame('2026-11-22', $egreso->fecha->toDateString());
        $this->assertEquals(96.5, $egreso->monto);
        $this->assertSame($this->admin->id, $egreso->user_id);

        $this->get(route('admin.egresos.index'))->assertOk()->assertSee('2 balones')->assertSee('S/ 96.50');

        $this->put(route('admin.egresos.update', $egreso->id), [
            'fecha' => '2026-11-21', 'categoria_egreso_id' => $gas->id, 'descripcion' => '2 balones de gas', 'monto' => '100',
        ])->assertRedirect();
        $this->assertSame('2026-11-21', $egreso->fresh()->fecha->toDateString());

        // No se aceptan egresos en el futuro ni sin monto
        $this->post(route('admin.egresos.store'), ['fecha' => '2026-11-30', 'categoria_egreso_id' => $gas->id, 'descripcion' => 'x', 'monto' => 0])
            ->assertSessionHasErrors(['fecha', 'monto']);

        $this->delete(route('admin.egresos.destroy', $egreso->id))->assertRedirect();
        $this->assertSame(0, Egreso::count());
    }

    public function test_tipos_de_egreso_propios(): void
    {
        $this->actingAs($this->admin)->post(route('admin.egresos.categorias.store'), ['nombre' => 'Luz'])->assertSessionHas('success');
        $luz = CategoriaEgreso::where('nombre', 'Luz')->sole();

        $this->post(route('admin.egresos.categorias.store'), ['nombre' => 'Luz'])->assertSessionHasErrors('nombre');

        $this->egreso('2026-11-22', 'Luz', 120);
        $this->delete(route('admin.egresos.categorias.destroy', $luz->id))->assertSessionHas('error');
        $this->assertNotNull($luz->fresh());

        Egreso::query()->delete();
        $this->delete(route('admin.egresos.categorias.destroy', $luz->id))->assertSessionHas('success');
        $this->assertNull($luz->fresh());
    }

    public function test_egresos_por_tipo_y_excel(): void
    {
        $this->egreso('2026-11-22', 'Backus (cerveza)', 300);
        $this->egreso('2026-11-20', 'Backus (cerveza)', 200);
        $this->egreso('2026-11-19', 'Pagos al personal', 800);
        $this->egreso('2026-10-30', 'Agua', 50);

        $respuesta = $this->actingAs($this->admin)->get(route('admin.egresos.index', ['periodo' => 'mes']))->assertOk();
        $this->assertEquals(1300, $respuesta->viewData('total'));
        $this->assertSame(['Pagos al personal', 'Backus (cerveza)'], array_column($respuesta->viewData('porCategoria'), 'nombre'));
        $this->assertEquals(500, $respuesta->viewData('porCategoria')[1]['total']);

        $backus = CategoriaEgreso::where('nombre', 'Backus (cerveza)')->value('id');
        $this->assertEquals(500, $this->get(route('admin.egresos.index', ['periodo' => 'mes', 'categoria' => $backus]))->viewData('total'));

        $csv = $this->get(route('admin.egresos.exportar', ['periodo' => 'mes']))->assertOk()->streamedContent();
        $this->assertStringContainsString('Pagos al personal', $csv);
        $this->assertStringContainsString('TOTAL;;;;;;1300.00', $csv);

        $this->venta('2026-11-22 12:00:00', ['yape' => 45]);
        $csv = $this->get(route('admin.ingresos.exportar', ['periodo' => 'dia']))->assertOk()->streamedContent();
        $this->assertStringContainsString('22/11/2026;1;0.00;45.00', $csv);
    }

    public function test_solo_el_admin_entra(): void
    {
        $cajera = User::factory()->create(['role_id' => Role::create(['nombre' => 'cajera'])->id]);

        $this->actingAs($cajera)->get(route('admin.ingresos.index'))->assertForbidden();
        $this->actingAs($cajera)->get(route('admin.egresos.index'))->assertForbidden();
    }
}
