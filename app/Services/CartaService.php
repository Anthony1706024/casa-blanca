<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

// ==========================================
// CARTA DIGITAL: ordena los productos disponibles en las mismas secciones
// de la carta impresa (public/docs/carta-casa-blanca.pdf).
// Lo que no encaje en ninguna sección se agrega al final por categoría,
// así un producto nuevo nunca desaparece de la carta.
// ==========================================
class CartaService
{
    // Orden y títulos de la carta impresa. Se compara por nombre de categoría y,
    // si se indica, de subcategoría (sin distinguir mayúsculas).
    public const SECCIONES = [
        ['chip' => 'Leche de tigre', 'script' => 'Shots de Leche de Tigre', 'titulo' => null, 'categoria' => 'Platos Marinos', 'subcategorias' => ['Shots de Leche de Tigre']],
        ['chip' => 'Brochetas', 'script' => 'Brochetas a la Parrilla', 'titulo' => null, 'categoria' => 'Platos Marinos', 'subcategorias' => ['Brochetas a la Parrilla']],
        ['chip' => 'Camarones', 'script' => 'Nuestra Especialidad', 'titulo' => 'Camarones', 'categoria' => 'Platos Marinos', 'subcategorias' => ['Para Compartir', 'Camarones - Plato Personal', 'Fuentes de Camarones']],
        ['chip' => 'Pescados y mariscos', 'script' => 'Pescados y', 'titulo' => 'Mariscos', 'categoria' => 'Platos Marinos', 'subcategorias' => ['Plato Personal', 'Fuentes']],
        ['chip' => 'Criollos', 'script' => 'Platos', 'titulo' => 'Criollos', 'categoria' => 'Platos Criollos', 'subcategorias' => ['Para Compartir', 'Plato Personal']],
        ['chip' => 'Gaseosas', 'script' => null, 'titulo' => 'Gaseosas', 'categoria' => 'Bebidas', 'subcategorias' => null],
        ['chip' => 'Cervezas', 'script' => null, 'titulo' => 'Cervezas', 'categoria' => 'Cervezas y otros tragos', 'subcategorias' => null],
        ['chip' => 'Vinos', 'script' => null, 'titulo' => 'Vinos', 'categoria' => 'Vinos', 'subcategorias' => null],
        ['chip' => 'Cremoladas', 'script' => null, 'titulo' => 'Cremoladas', 'categoria' => 'Cremoladas', 'subcategorias' => null],
        ['chip' => 'Guarniciones', 'script' => null, 'titulo' => 'Guarniciones', 'categoria' => 'Porciones', 'subcategorias' => null],
    ];

    private const PREFIJO_FUENTE = 'fuente de ';

    // Productos de la carta (Producto::disponiblesParaVenta) agrupados en secciones listas para pintar
    public function secciones(Collection $productos): array
    {
        $productos = $productos->loadMissing('subcategoria')->values();
        $usados = [];
        $secciones = [];

        foreach (self::SECCIONES as $config) {
            $grupo = $this->productosDe($productos, $config, $usados);
            if ($grupo->isNotEmpty()) {
                $secciones[] = $this->armar($config, $grupo);
            }
        }

        // Lo que no encajó: una sección por categoría, con su propio nombre
        $productos->reject(fn ($producto) => isset($usados[$producto->id]))
            ->groupBy(fn ($producto) => $producto->categoria->nombre ?? 'Otros')
            ->each(function ($grupo, $categoria) use (&$secciones) {
                $secciones[] = $this->armar(['chip' => $categoria, 'script' => null, 'titulo' => $categoria], $grupo->values());
            });

        // Los anclas deben ser únicas aunque dos secciones se llamen parecido
        foreach ($secciones as $indice => &$seccion) {
            $seccion['id'] = 'sec-'.$indice.'-'.Str::slug($seccion['chip']);
        }

        return $secciones;
    }

    // Imagen de portada: un plato destacado con foto o, si no hay, el primero con foto
    public function portada(Collection $productos): ?Producto
    {
        $conFoto = $productos->filter(fn ($producto) => $producto->imagen);

        return $conFoto->firstWhere('destacado', true) ?? $conFoto->first();
    }

    private function productosDe(Collection $productos, array $config, array &$usados): Collection
    {
        $normalizar = fn (?string $texto) => mb_strtolower(trim((string) $texto));
        $subcategorias = $config['subcategorias'] ? array_map($normalizar, $config['subcategorias']) : null;

        $grupo = $productos->filter(function ($producto) use ($config, $subcategorias, $normalizar, $usados) {
            if (isset($usados[$producto->id]) || $normalizar($producto->categoria->nombre ?? null) !== $normalizar($config['categoria'])) {
                return false;
            }

            return $subcategorias === null || in_array($normalizar($producto->subcategoria->nombre ?? null), $subcategorias, true);
        });

        // Se respeta el orden de subcategorías de la carta impresa; dentro de cada una, por nombre
        if ($subcategorias !== null) {
            $grupo = $grupo->sortBy(fn ($producto) => array_search($normalizar($producto->subcategoria->nombre ?? null), $subcategorias, true));
        }

        foreach ($grupo as $producto) {
            $usados[$producto->id] = true;
        }

        return $grupo->values();
    }

    private function armar(array $config, Collection $productos): array
    {
        $porNombre = $productos->keyBy(fn ($producto) => mb_strtolower($producto->nombre));
        $emparejados = [];
        $fuentes = [];

        // "Fuente de X" se muestra en la misma fila que "X", como en la carta impresa (Personal / Fuente)
        foreach ($productos as $producto) {
            $nombre = mb_strtolower($producto->nombre);
            if (str_starts_with($nombre, self::PREFIJO_FUENTE)) {
                $personal = $porNombre->get(mb_substr($nombre, mb_strlen(self::PREFIJO_FUENTE)));
                if ($personal) {
                    $fuentes[$personal->id] = $producto;
                    $emparejados[$producto->id] = true;
                }
            }
        }

        $filas = [];
        foreach ($productos as $producto) {
            if (isset($emparejados[$producto->id])) {
                continue;
            }

            $fuente = $fuentes[$producto->id] ?? null;
            $filas[] = [
                'producto' => $producto,
                'descripcion' => $this->descripcion($producto),
                'variantes' => $fuente
                    ? [['producto' => $producto, 'etiqueta' => 'Personal'], ['producto' => $fuente, 'etiqueta' => 'Fuente']]
                    : [['producto' => $producto, 'etiqueta' => null]],
            ];
        }

        return [
            'chip' => $config['chip'],
            'script' => $config['script'],
            'titulo' => $config['titulo'],
            'foto' => $productos->first(fn ($producto) => $producto->imagen),
            'filas' => $filas,
        ];
    }

    // La descripción solo se muestra si dice algo más que el nombre
    private function descripcion(Producto $producto): ?string
    {
        $descripcion = trim((string) $producto->descripcion);
        $nombre = mb_strtolower($producto->nombre);
        $texto = mb_strtolower($descripcion);

        if ($descripcion === '' || $texto === $nombre || str_contains($nombre, $texto) || str_contains($texto, 'fuente de')) {
            return null;
        }

        return $descripcion;
    }
}
