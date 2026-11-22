<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaEgreso;
use App\Models\Egreso;
use App\Models\Pago;
use App\Services\FinanzasService;
use App\Support\Periodo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Egresos: compras, pagos a distribuidores y al personal, arreglos, servicios y otros gastos
class EgresoController extends Controller
{
    public function __construct(private FinanzasService $finanzas) {}

    public function index(Request $request)
    {
        $periodo = Periodo::desdeRequest($request);

        $query = $this->filtrados($request, $periodo);
        $total = (float) (clone $query)->sum('monto');
        $egresos = $query->with(['categoria', 'usuario'])->orderByDesc('fecha')->orderByDesc('id')->paginate(30)->withQueryString();

        return view('admin.egresos.index', [
            'periodo' => $periodo,
            'egresos' => $egresos,
            'total' => $total,
            'porCategoria' => $this->finanzas->egresosPorCategoria($periodo),
            'categorias' => CategoriaEgreso::withCount('egresos')->orderBy('nombre')->get(),
            'metodos' => Pago::METODOS,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $egreso = Egreso::create($datos + ['user_id' => $request->user()->id]);

        return redirect()->route('admin.egresos.index', ['periodo' => 'dia', 'fecha' => $egreso->fecha->toDateString()])
            ->with('success', 'Egreso registrado: S/ '.number_format($egreso->monto, 2).' en '.$egreso->categoria->nombre.'.');
    }

    public function edit($id)
    {
        return view('admin.egresos.edit', [
            'egreso' => Egreso::findOrFail($id),
            'categorias' => CategoriaEgreso::orderBy('nombre')->get(),
            'metodos' => Pago::METODOS,
        ]);
    }

    public function update(Request $request, $id)
    {
        $egreso = Egreso::findOrFail($id);
        $egreso->update($this->validar($request));

        return redirect()->route('admin.egresos.index', ['periodo' => 'dia', 'fecha' => $egreso->fecha->toDateString()])
            ->with('success', 'Egreso actualizado.');
    }

    public function destroy($id)
    {
        Egreso::findOrFail($id)->delete();

        return back()->with('success', 'Egreso eliminado.');
    }

    public function exportar(Request $request)
    {
        $periodo = Periodo::desdeRequest($request);
        $egresos = $this->filtrados($request, $periodo)->with('categoria')->orderBy('fecha')->orderBy('id')->get();

        $lineas = $egresos->map(fn ($egreso) => [
            $egreso->fecha->format('d/m/Y'),
            $egreso->categoria->nombre,
            $egreso->descripcion,
            $egreso->proveedor,
            $egreso->metodo_nombre,
            $egreso->comprobante,
            IngresoController::numero((float) $egreso->monto),
        ])->all();
        $lineas[] = ['TOTAL', '', '', '', '', '', IngresoController::numero((float) $egresos->sum('monto'))];

        return IngresoController::csv(
            'egresos-'.$periodo->inicio->toDateString().'-al-'.$periodo->fin->toDateString().'.csv',
            'Egresos - '.$periodo->etiqueta(),
            ['Fecha', 'Categoría', 'Descripción', 'Proveedor', 'Pagado con', 'Comprobante', 'Monto'],
            $lineas,
        );
    }

    public function categoriasStore(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:80', Rule::unique('categorias_egreso', 'nombre')],
        ], [], ['nombre' => 'nombre de la categoría']);

        CategoriaEgreso::create(['nombre' => trim($datos['nombre'])]);

        return back()->with('success', 'Categoría "'.trim($datos['nombre']).'" agregada.');
    }

    public function categoriasDestroy($id)
    {
        $categoria = CategoriaEgreso::withCount('egresos')->findOrFail($id);
        if ($categoria->egresos_count > 0) {
            return back()->with('error', 'La categoría "'.$categoria->nombre.'" tiene egresos registrados: no se puede eliminar.');
        }
        $categoria->delete();

        return back()->with('success', 'Categoría eliminada.');
    }

    private function filtrados(Request $request, Periodo $periodo)
    {
        $query = $this->finanzas->egresos($periodo);

        if ($request->filled('categoria')) {
            $query->where('categoria_egreso_id', $request->integer('categoria'));
        }
        if ($request->filled('buscar')) {
            $buscar = trim($request->buscar);
            $query->where(fn ($q) => $q->where('descripcion', 'LIKE', "%{$buscar}%")->orWhere('proveedor', 'LIKE', "%{$buscar}%"));
        }

        return $query;
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'categoria_egreso_id' => ['required', 'exists:categorias_egreso,id'],
            'descripcion' => ['required', 'string', 'max:255'],
            'proveedor' => ['nullable', 'string', 'max:120'],
            'metodo' => ['nullable', Rule::in(array_keys(Pago::METODOS))],
            'comprobante' => ['nullable', 'string', 'max:60'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
        ], [], [
            'categoria_egreso_id' => 'categoría',
            'descripcion' => 'descripción',
            'metodo' => 'forma de pago',
        ]);
    }
}
