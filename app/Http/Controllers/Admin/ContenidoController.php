<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contenido;
use App\Models\SitioItem;
use App\Services\ImagenService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContenidoController extends Controller
{
    public function __construct(private ImagenService $imagenes) {}

    private const REGLA_IMAGEN = 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192';

    private const MENSAJES_IMAGEN = [
        'image' => 'El archivo debe ser una imagen.',
        'mimes' => 'La imagen debe ser JPG, PNG o WEBP.',
        'max' => 'La imagen no debe pesar más de 8MB.',
        'required_if' => 'Esta sección necesita una imagen.',
    ];

    // ==========================================
    // TEXTOS, PORTADA Y REDES
    // ==========================================
    public function index()
    {
        $grupos = collect(Contenido::CAMPOS)->groupBy(fn ($campo) => $campo[0], true);
        $valores = Contenido::valores();

        return view('admin.contenido.index', compact('grupos', 'valores'));
    }

    public function guardar(Request $request)
    {
        $reglas = [];
        foreach (Contenido::CAMPOS as $clave => $campo) {
            $reglas[$clave] = match ($campo[2]) {
                'imagen' => self::REGLA_IMAGEN,
                'url' => 'nullable|url|max:255',
                'whatsapp' => ['nullable', 'regex:/^[0-9]{9,15}$/'],
                'parrafo' => 'nullable|string|max:1000',
                default => 'nullable|string|max:255',
            };
        }
        $reglas['hero_titulo'] = 'required|string|max:255';

        $datos = $request->validate($reglas, [
            ...self::MENSAJES_IMAGEN,
            'url' => 'Escribe un enlace completo, con https://',
            'whatsapp.regex' => 'El WhatsApp debe tener solo números, con código de país (ej: 51987654321).',
            'hero_titulo.required' => 'El título de la portada es obligatorio.',
        ]);

        foreach (Contenido::CAMPOS as $clave => $campo) {
            if ($campo[2] !== 'imagen') {
                Contenido::updateOrCreate(['clave' => $clave], ['valor' => $datos[$clave] ?? null]);
            } elseif ($request->hasFile($clave)) {
                $anterior = Contenido::valores()[$clave];
                Contenido::updateOrCreate(['clave' => $clave], ['valor' => $this->imagenes->guardar($request->file($clave), 'web', $clave)]);

                // Las imágenes propias del sistema (public/img) no se borran
                if ($anterior && ! str_starts_with($anterior, 'img/')) {
                    $this->imagenes->eliminar($anterior);
                }
            }
        }

        return redirect()->route('admin.contenido.index')
            ->with('success', 'Contenido de la página actualizado');
    }

    // ==========================================
    // INSTALACIONES, GALERÍA Y TESTIMONIOS
    // ==========================================
    public function items($tipo)
    {
        $config = SitioItem::TIPOS[$tipo];
        $items = SitioItem::where('tipo', $tipo)->orderBy('orden')->orderBy('id')->get();

        return view('admin.contenido.items', compact('tipo', 'config', 'items'));
    }

    public function itemStore(Request $request, $tipo)
    {
        $datos = $this->validarItem($request, $tipo, true);
        $datos['tipo'] = $tipo;

        if ($request->hasFile('imagen')) {
            $datos['imagen'] = $this->imagenes->guardar($request->file('imagen'), 'web');
        }

        SitioItem::create($datos);

        return redirect()->route('admin.contenido.items', $tipo)
            ->with('success', 'Elemento agregado a la página');
    }

    public function itemEdit($id)
    {
        $item = SitioItem::findOrFail($id);
        $config = SitioItem::TIPOS[$item->tipo];

        return view('admin.contenido.item-edit', compact('item', 'config'));
    }

    public function itemUpdate(Request $request, $id)
    {
        $item = SitioItem::findOrFail($id);
        $datos = $this->validarItem($request, $item->tipo, false);

        if ($request->hasFile('imagen')) {
            $this->imagenes->eliminar($item->imagen);
            $datos['imagen'] = $this->imagenes->guardar($request->file('imagen'), 'web');
        }

        $item->update($datos);

        return redirect()->route('admin.contenido.items', $item->tipo)
            ->with('success', 'Elemento actualizado');
    }

    public function itemDestroy($id)
    {
        $item = SitioItem::findOrFail($id);
        $tipo = $item->tipo;
        $item->delete();

        return redirect()->route('admin.contenido.items', $tipo)
            ->with('success', 'Elemento eliminado');
    }

    private function validarItem(Request $request, string $tipo, bool $creando): array
    {
        $datos = $request->validate([
            'titulo' => 'required|string|max:150',
            'descripcion' => [Rule::requiredIf($tipo === 'testimonio'), 'nullable', 'string', 'max:500'],
            'detalle' => 'nullable|string|max:150',
            'orden' => 'nullable|integer|min:0|max:9999',
            'imagen' => $creando && $tipo === 'galeria' ? str_replace('nullable', 'required', self::REGLA_IMAGEN) : self::REGLA_IMAGEN,
        ], [
            ...self::MENSAJES_IMAGEN,
            'titulo.required' => 'Este campo es obligatorio.',
            'descripcion.required' => 'Escribe el testimonio.',
            'imagen.required' => 'Esta sección necesita una imagen.',
        ]);

        unset($datos['imagen']);
        $datos['orden'] = $datos['orden'] ?? 0;
        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }
}
