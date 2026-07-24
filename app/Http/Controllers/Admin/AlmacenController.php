<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Subcategoria;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AlmacenController extends Controller
{
    // ==========================================
    // VER ALMACÉN
    // ==========================================
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'subcategoria']);

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
        $ultimoProducto = Producto::orderBy('id', 'desc')->first();
        if ($ultimoProducto) {
            $numero = intval(substr($ultimoProducto->codigo, 4)) + 1;
            $codigoSugerido = 'PRO-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        } else {
            $codigoSugerido = 'PRO-000001';
        }
        
        return view('admin.almacen.create', compact('categorias', 'subcategorias', 'codigoSugerido'));
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
            'stock' => 'required|integer|min:0',
            'precio_compra' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'codigo.unique' => 'El código ya está registrado en otro producto.',
            'codigo.required' => 'El código es obligatorio.',
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'categoria_id.required' => 'Debes seleccionar una categoría.',
            'stock.required' => 'El stock inicial es obligatorio.',
            'stock.min' => 'El stock no puede ser negativo.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser JPG, PNG, JPEG o GIF.',
            'imagen.max' => 'La imagen no debe pesar más de 2MB.',
        ]);

        $data = $request->all();

        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            
            // Generar nombre único con extensión original
            $nombreImagen = 'productos/' . Str::uuid() . '.' . $imagen->getClientOriginalExtension();
            
            // Guardar la imagen en su formato original
            $imagen->storeAs(
                'productos',
                basename($nombreImagen),
                'public'
            );
            
            $data['imagen'] = $nombreImagen;
        }

        Producto::create($data);

        return redirect()->route('admin.almacen.index')
            ->with('success', 'Producto registrado exitosamente');
    }

    // ==========================================
    // FORMULARIO PARA EDITAR PRODUCTO
    // ==========================================
    public function edit($id)
    {
        $producto = Producto::findOrFail($id);
        $categorias = Categoria::all();
        $subcategorias = Subcategoria::all();
        return view('admin.almacen.edit', compact('producto', 'categorias', 'subcategorias'));
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
            'stock' => 'required|integer|min:0',
            'precio_compra' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'codigo.unique' => 'El código ya está registrado en otro producto.',
            'codigo.required' => 'El código es obligatorio.',
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'categoria_id.required' => 'Debes seleccionar una categoría.',
            'stock.required' => 'El stock es obligatorio.',
            'stock.min' => 'El stock no puede ser negativo.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser JPG, PNG, JPEG o GIF.',
            'imagen.max' => 'La imagen no debe pesar más de 2MB.',
        ]);

        $data = $request->except(['imagen']);

        if ($request->hasFile('imagen')) {
            // Eliminar la imagen anterior si existe
            if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                Storage::disk('public')->delete($producto->imagen);
            }
            
            $imagen = $request->file('imagen');
            
            // Generar nombre único con extensión original
            $nombreImagen = 'productos/' . Str::uuid() . '.' . $imagen->getClientOriginalExtension();
            
            // Guardar la imagen en su formato original
            $imagen->storeAs(
                'productos',
                basename($nombreImagen),
                'public'
            );
            
            $data['imagen'] = $nombreImagen;
        }

        $producto->update($data);

        return redirect()->route('admin.almacen.index')
            ->with('success', 'Producto actualizado correctamente');
    }

    // ==========================================
    // ELIMINAR PRODUCTO
    // ==========================================
    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        
        if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
            Storage::disk('public')->delete($producto->imagen);
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
}