<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Insumo;
use App\Models\Subcategoria;
use App\Services\ImagenService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class AlmacenController extends Controller
{
    // ==========================================
    // VER ALMACÉN
    // ==========================================
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'subcategoria', 'insumos']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('codigo', 'LIKE', "%{$search}%")
                  ->orWhere('nombre', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }

        if ($request->filled('subcategoria')) {
            $query->where('subcategoria_id', $request->subcategoria);
        }

        $productos = $query->orderBy('id', 'desc')->paginate(15);
        $categorias = Categoria::all();
        $subcategorias = Subcategoria::all();

        return view('admin.almacen.index', compact('productos', 'categorias', 'subcategorias'));
    }

    // ==========================================
    // ACTUALIZAR STOCK
    // ==========================================
    public function updateStock(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        if ($producto->esPlato()) {
            return redirect()->route('admin.almacen.index')
                ->with('error', 'Este producto usa receta: su stock depende de los insumos');
        }

        $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $producto->update(['stock' => $request->stock]);

        return redirect()->route('admin.almacen.index')
            ->with('success', 'Stock actualizado correctamente');
    }

    // ==========================================
    // FORMULARIO PARA REGISTRAR PRODUCTO
    // ==========================================
    public function create()
    {
        $categorias = Categoria::all();
        $subcategorias = Subcategoria::all();
        $insumos = Insumo::orderBy('nombre')->get();
        $ultimoProducto = Producto::orderBy('id', 'desc')->first();
        if ($ultimoProducto) {
            $numero = intval(substr($ultimoProducto->codigo, 4)) + 1;
            $codigoSugerido = 'PRO-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        } else {
            $codigoSugerido = 'PRO-000001';
        }
        
        return view('admin.almacen.create', compact('categorias', 'subcategorias', 'insumos', 'codigoSugerido'));
    }

    // ==========================================
    // GUARDAR PRODUCTO
    // ==========================================
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => ['required', 'string', 'max:50', Rule::unique('productos', 'codigo')],
            'nombre' => 'required|string|max:200',
            'categoria_id' => 'required|exists:categorias,id',
            'subcategoria_id' => 'nullable|exists:subcategorias,id',
            ...$this->reglasTipo(),
            'precio_compra' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
        ], [
            'codigo.unique' => 'El código ya está registrado en otro producto.',
            'codigo.required' => 'El código es obligatorio.',
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'categoria_id.required' => 'Debes seleccionar una categoría.',
            'stock.required_if' => 'El stock inicial es obligatorio.',
            'stock.min' => 'El stock no puede ser negativo.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser JPG, PNG, GIF o WEBP.',
            'imagen.max' => 'La imagen no debe pesar más de 8MB.',
            ...$this->mensajesTipo(),
        ]);

        $data = $request->all();

        // Un plato no maneja stock propio: depende de sus insumos
        if ($request->tipo === Producto::PLATO) {
            $data['stock'] = 0;
        }

        if ($request->hasFile('imagen')) {
            $data['imagen'] = app(ImagenService::class)->guardar($request->file('imagen'), 'productos');
        }

        DB::transaction(function () use ($data, $request) {
            $producto = Producto::create($data);
            $this->guardarReceta($producto, $request);
        });

        return redirect()->route('admin.almacen.index')
            ->with('success', 'Producto registrado exitosamente');
    }

    // ==========================================
    // FORMULARIO PARA EDITAR PRODUCTO
    // ==========================================
    public function edit($id)
    {
        $producto = Producto::with('insumos')->findOrFail($id);
        $categorias = Categoria::all();
        $subcategorias = Subcategoria::all();
        $insumos = Insumo::orderBy('nombre')->get();
        return view('admin.almacen.edit', compact('producto', 'categorias', 'subcategorias', 'insumos'));
    }

    // ==========================================
    // ACTUALIZAR PRODUCTO
    // ==========================================
    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'codigo' => ['required', 'string', 'max:50', Rule::unique('productos', 'codigo')->ignore($id)],
            'nombre' => 'required|string|max:200',
            'categoria_id' => 'required|exists:categorias,id',
            'subcategoria_id' => 'nullable|exists:subcategorias,id',
            ...$this->reglasTipo(),
            'precio_compra' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
        ], [
            'codigo.unique' => 'El código ya está registrado en otro producto.',
            'codigo.required' => 'El código es obligatorio.',
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'categoria_id.required' => 'Debes seleccionar una categoría.',
            'stock.required_if' => 'El stock es obligatorio.',
            'stock.min' => 'El stock no puede ser negativo.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser JPG, PNG, GIF o WEBP.',
            'imagen.max' => 'La imagen no debe pesar más de 8MB.',
            ...$this->mensajesTipo(),
        ]);

        $data = $request->except(['imagen']);

        // Un plato no maneja stock propio: depende de sus insumos
        if ($request->tipo === Producto::PLATO) {
            $data['stock'] = 0;
        }

        if ($request->hasFile('imagen')) {
            // Eliminar la imagen anterior si existe
            app(ImagenService::class)->eliminar($producto->imagen);

            $data['imagen'] = app(ImagenService::class)->guardar($request->file('imagen'), 'productos');
        }

        DB::transaction(function () use ($producto, $data, $request) {
            $producto->update($data);
            $this->guardarReceta($producto, $request);
        });

        return redirect()->route('admin.almacen.index')
            ->with('success', 'Producto actualizado correctamente');
    }

    // ==========================================
    // ELIMINAR PRODUCTO
    // ==========================================
    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);

        if ($producto->pedidoItems()->exists()) {
            return redirect()->route('admin.almacen.index')
                ->with('error', 'No puedes eliminar un producto que ya tiene pedidos registrados');
        }

        $producto->delete();

        return redirect()->route('admin.almacen.index')
            ->with('success', 'Producto eliminado correctamente');
    }

    // ==========================================
    // CATEGORÍAS
    // ==========================================
    public function categoriasIndex()
    {
        $categorias = Categoria::withCount('productos')->get();
        return view('admin.almacen.categorias', compact('categorias'));
    }

    public function categoriasStore(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias,nombre',
            'descripcion' => 'nullable|string|max:255',
        ]);

        Categoria::create($request->all());

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría creada exitosamente');
    }

    public function categoriasDestroy($id)
    {
        $categoria = Categoria::findOrFail($id);
        
        if ($categoria->productos()->count() > 0) {
            return redirect()->route('admin.categorias.index')
                ->with('error', 'No puedes eliminar una categoría con productos asociados');
        }

        $categoria->delete();

        return redirect()->route('admin.categorias.index')
            ->with('success', 'Categoría eliminada correctamente');
    }

    // ==========================================
    // SUBCATEGORÍAS
    // ==========================================
    public function subcategoriasIndex()
    {
        $subcategorias = Subcategoria::with('categoria')->get();
        $categorias = Categoria::all();
        return view('admin.subcategorias.index', compact('subcategorias', 'categorias'));
    }

    public function subcategoriasStore(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:subcategorias,nombre',
            'descripcion' => 'nullable|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
        ]);

        Subcategoria::create($request->all());

        return redirect()->route('admin.subcategorias.index')
            ->with('success', 'Subcategoría creada exitosamente');
    }

    public function subcategoriasDestroy($id)
    {
        $subcategoria = Subcategoria::findOrFail($id);
        
        if ($subcategoria->productos()->count() > 0) {
            return redirect()->route('admin.subcategorias.index')
                ->with('error', 'No puedes eliminar una subcategoría con productos asociados');
        }

        $subcategoria->delete();

        return redirect()->route('admin.subcategorias.index')
            ->with('success', 'Subcategoría eliminada correctamente');
    }

    // API para obtener subcategorías por categoría
    public function getSubcategorias($categoria_id)
    {
        $subcategorias = Subcategoria::where('categoria_id', $categoria_id)->get();
        return response()->json($subcategorias);
    }

    // ==========================================
    // TIPO DE PRODUCTO Y RECETA
    // ==========================================
    private function reglasTipo(): array
    {
        return [
            'tipo' => ['required', Rule::in([Producto::DIRECTO, Producto::PLATO])],
            'stock' => 'exclude_if:tipo,plato|required_if:tipo,directo|integer|min:0',
            'receta' => 'exclude_if:tipo,directo|required_if:tipo,plato|array|min:1',
            'receta.*.insumo_id' => 'exclude_if:tipo,directo|required|distinct|exists:insumos,id',
            'receta.*.cantidad' => 'exclude_if:tipo,directo|required|numeric|gt:0|max:999999',
        ];
    }

    private function mensajesTipo(): array
    {
        return [
            'receta.required_if' => 'Un plato debe tener al menos un insumo en su receta.',
            'receta.min' => 'Un plato debe tener al menos un insumo en su receta.',
            'receta.*.insumo_id.required' => 'Selecciona el insumo.',
            'receta.*.insumo_id.distinct' => 'No repitas el mismo insumo en la receta.',
            'receta.*.cantidad.required' => 'Indica la cantidad del insumo.',
            'receta.*.cantidad.gt' => 'La cantidad del insumo debe ser mayor a cero.',
        ];
    }

    private function guardarReceta(Producto $producto, Request $request): void
    {
        $receta = [];

        if ($request->tipo === Producto::PLATO) {
            foreach ($request->receta as $linea) {
                $receta[$linea['insumo_id']] = ['cantidad' => $linea['cantidad'], 'visible_cliente' => ! empty($linea['visible'])];
            }
        }

        $producto->insumos()->sync($receta);
    }
}