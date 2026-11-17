<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaInsumo;
use App\Models\Insumo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InsumoController extends Controller
{
    // ==========================================
    // LISTA DE INSUMOS
    // ==========================================
    public function index(Request $request)
    {
        $query = Insumo::with('categoria');

        if ($request->filled('search')) {
            $query->where('nombre', 'LIKE', "%{$request->search}%");
        }

        if ($request->filled('categoria')) {
            $query->where('categoria_insumo_id', $request->categoria);
        }

        if ($request->boolean('bajo')) {
            $query->whereColumn('stock', '<=', 'stock_minimo');
        }

        $insumos = $query->orderBy('nombre')->paginate(15)->withQueryString();
        $categorias = CategoriaInsumo::orderBy('nombre')->get();

        return view('admin.insumos.index', compact('insumos', 'categorias'));
    }

    // ==========================================
    // CREAR INSUMO
    // ==========================================
    public function create()
    {
        $categorias = CategoriaInsumo::orderBy('nombre')->get();
        $unidades = Insumo::UNIDADES;

        return view('admin.insumos.create', compact('categorias', 'unidades'));
    }

    public function store(Request $request)
    {
        Insumo::create($this->validar($request));

        return redirect()->route('admin.insumos.index')
            ->with('success', 'Insumo registrado exitosamente');
    }

    // ==========================================
    // EDITAR INSUMO
    // ==========================================
    public function edit($id)
    {
        $insumo = Insumo::findOrFail($id);
        $categorias = CategoriaInsumo::orderBy('nombre')->get();
        $unidades = Insumo::UNIDADES;

        return view('admin.insumos.edit', compact('insumo', 'categorias', 'unidades'));
    }

    public function update(Request $request, $id)
    {
        $insumo = Insumo::findOrFail($id);
        $insumo->update($this->validar($request, $insumo->id));

        return redirect()->route('admin.insumos.index')
            ->with('success', 'Insumo actualizado correctamente');
    }

    // ==========================================
    // INGRESO DE MERCADERÍA: suma al stock actual
    // ==========================================
    public function ingreso(Request $request, $id)
    {
        $insumo = Insumo::findOrFail($id);

        $request->validate([
            'cantidad' => 'required|numeric|gt:0|max:999999',
        ], [
            'cantidad.required' => 'Indica la cantidad que ingresa.',
            'cantidad.gt' => 'La cantidad debe ser mayor a cero.',
        ]);

        $insumo->increment('stock', $request->cantidad);

        return redirect()->back()
            ->with('success', "Ingreso registrado: {$insumo->nombre} +".Insumo::formatear($request->cantidad)." {$insumo->unidad}");
    }

    // ==========================================
    // ELIMINAR INSUMO
    // ==========================================
    public function destroy($id)
    {
        $insumo = Insumo::findOrFail($id);

        if ($insumo->productos()->exists()) {
            return redirect()->route('admin.insumos.index')
                ->with('error', 'No puedes eliminar un insumo que se usa en la receta de un producto');
        }

        $insumo->delete();

        return redirect()->route('admin.insumos.index')
            ->with('success', 'Insumo eliminado correctamente');
    }

    // ==========================================
    // CATEGORÍAS DE INSUMO
    // ==========================================
    public function categoriasIndex()
    {
        $categorias = CategoriaInsumo::withCount('insumos')->orderBy('nombre')->get();

        return view('admin.insumos.categorias', compact('categorias'));
    }

    public function categoriasStore(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias_insumo,nombre',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Esa categoría ya existe.',
        ]);

        CategoriaInsumo::create($request->only('nombre'));

        return redirect()->route('admin.insumos.categorias.index')
            ->with('success', 'Categoría creada exitosamente');
    }

    public function categoriasDestroy($id)
    {
        $categoria = CategoriaInsumo::findOrFail($id);

        if ($categoria->insumos()->exists()) {
            return redirect()->route('admin.insumos.categorias.index')
                ->with('error', 'No puedes eliminar una categoría con insumos asociados');
        }

        $categoria->delete();

        return redirect()->route('admin.insumos.categorias.index')
            ->with('success', 'Categoría eliminada correctamente');
    }

    private function validar(Request $request, $ignorarId = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:150', Rule::unique('insumos', 'nombre')->ignore($ignorarId)],
            'categoria_insumo_id' => 'required|exists:categorias_insumo,id',
            'unidad' => ['required', Rule::in(Insumo::UNIDADES)],
            'stock' => 'required|numeric|min:0|max:999999',
            'stock_minimo' => 'required|numeric|min:0|max:999999',
        ], [
            'nombre.required' => 'El nombre del insumo es obligatorio.',
            'nombre.unique' => 'Ya existe un insumo con ese nombre.',
            'categoria_insumo_id.required' => 'Debes seleccionar una categoría.',
            'unidad.required' => 'Debes seleccionar la unidad de medida.',
            'stock.required' => 'El stock es obligatorio.',
            'stock.min' => 'El stock no puede ser negativo.',
            'stock_minimo.required' => 'El stock mínimo es obligatorio.',
        ]);
    }
}
