<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Subcategoria;
use Illuminate\Http\Request;

class SubcategoriaController extends Controller
{
    public function index()
    {
        $subcategorias = Subcategoria::with('categoria')->get();
        $categorias = Categoria::all();
        return view('admin.subcategorias.index', compact('subcategorias', 'categorias'));
    }

    public function store(Request $request)
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

    public function destroy($id)
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
    public function getByCategoria($categoria_id)
    {
        $subcategorias = Subcategoria::where('categoria_id', $categoria_id)->get();
        return response()->json($subcategorias);
    }
}