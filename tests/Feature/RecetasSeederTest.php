<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Insumo;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RecetasSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecetasSeederTest extends TestCase
{
    use RefreshDatabase;

    private Producto $ceviche;

    private Producto $gaseosa;

    protected function setUp(): void
    {
        parent::setUp();

        $categoria = Categoria::create(['nombre' => 'Platos']);
        $this->ceviche = Producto::create(['codigo' => 'C1', 'nombre' => 'Ceviche de pescado', 'tipo' => 'directo', 'categoria_id' => $categoria->id, 'stock' => 50, 'precio_venta' => 40]);
        $this->gaseosa = Producto::create(['codigo' => 'B1', 'nombre' => 'Inca Kola 1L', 'tipo' => 'directo', 'categoria_id' => $categoria->id, 'stock' => 50, 'precio_venta' => 10]);

        $this->seed(RecetasSeeder::class);
    }

    public function test_el_plato_queda_con_receta_y_el_cliente_no_ve_gramos_ni_condimentos(): void
    {
        $ceviche = $this->ceviche->fresh('insumos');

        $this->assertTrue($ceviche->esPlato());
        $this->assertEqualsCanonicalizing(['Pescado fresco', 'Cebolla', 'Camote', 'Choclo', 'Lechuga'], $ceviche->incluye);
        $this->assertSame(0, (int) $ceviche->stock);
    }

    public function test_el_precio_no_cambia_ni_queda_en_el_historial(): void
    {
        $this->assertEqualsWithDelta(40, (float) $this->ceviche->fresh()->precio_venta, 0.001);
        $this->assertSame(0, $this->ceviche->historialPrecios()->count());
    }

    public function test_vender_el_plato_y_la_gaseosa_descuenta_sus_insumos(): void
    {
        $cajera = User::factory()->create(['role_id' => Role::create(['nombre' => 'cajera'])->id]);
        $pescado = Insumo::where('nombre', 'Pescado fresco')->firstOrFail();
        $rocoto = Insumo::where('nombre', 'Rocoto')->firstOrFail();
        $bebida = Insumo::where('nombre', 'Inca Kola 1L')->firstOrFail();
        [$antesPescado, $antesRocoto, $antesBebida] = [(float) $pescado->stock, (float) $rocoto->stock, (float) $bebida->stock];

        $this->post(route('carta.store'), [
            'dni' => '12345678',
            'mesa' => '3',
            'items' => [$this->ceviche->id => 2, $this->gaseosa->id => 1],
        ])->assertSessionHasNoErrors();

        $this->actingAs($cajera)->post(route('cajera.pedidos.cobrar', Pedido::firstOrFail()->id), ['pagos' => [
            ['metodo' => 'efectivo', 'monto' => 90],
        ]])->assertSessionHasNoErrors();

        $this->assertEqualsWithDelta($antesPescado - 500, (float) $pescado->fresh()->stock, 0.001);
        $this->assertEqualsWithDelta($antesRocoto - 10, (float) $rocoto->fresh()->stock, 0.001);
        $this->assertEqualsWithDelta($antesBebida - 1, (float) $bebida->fresh()->stock, 0.001);
    }

    public function test_si_se_acaba_un_insumo_el_plato_sale_de_la_carta(): void
    {
        Insumo::where('nombre', 'Pescado fresco')->update(['stock' => 100]);

        $this->get(route('carta.index'))->assertOk()->assertDontSee('Ceviche de pescado');
    }

    public function test_ejecutarlo_de_nuevo_respeta_el_stock_y_las_recetas_ajustadas(): void
    {
        $pescado = Insumo::where('nombre', 'Pescado fresco')->firstOrFail();
        $pescado->update(['stock' => 777]);
        $this->ceviche->insumos()->updateExistingPivot($pescado->id, ['cantidad' => 300]);

        $this->seed(RecetasSeeder::class);

        $this->assertEqualsWithDelta(777, (float) $pescado->fresh()->stock, 0.001);
        $this->assertEqualsWithDelta(300, (float) $this->ceviche->insumos()->find($pescado->id)->pivot->cantidad, 0.001);
    }
}
