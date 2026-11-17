<?php

namespace App\Console\Commands;

use App\Models\Contenido;
use App\Models\Producto;
use App\Models\SitioItem;
use App\Services\ImagenService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ConvertirImagenes extends Command
{
    protected $signature = 'imagenes:convertir';

    protected $description = 'Convierte a WebP las imágenes ya guardadas y crea sus miniaturas. Se puede ejecutar varias veces.';

    private int $convertidas = 0;

    private int $errores = 0;

    public function handle(ImagenService $imagenes): int
    {
        if (! ImagenService::soportaWebp()) {
            $this->error('Este PHP no tiene soporte WebP en GD. No se cambió nada.');

            return self::FAILURE;
        }

        foreach (Producto::whereNotNull('imagen')->get() as $producto) {
            $this->procesar($imagenes, $producto->imagen, fn ($ruta) => $producto->update(['imagen' => $ruta]));
        }

        foreach (SitioItem::whereNotNull('imagen')->get() as $item) {
            $this->procesar($imagenes, $item->imagen, fn ($ruta) => $item->update(['imagen' => $ruta]));
        }

        $portada = Contenido::where('clave', 'hero_imagen')->first();
        if ($portada?->valor && ! str_starts_with($portada->valor, 'img/')) {
            $this->procesar($imagenes, $portada->valor, fn ($ruta) => $portada->update(['valor' => $ruta]));
        }

        $this->info("Convertidas: {$this->convertidas}. Con error: {$this->errores}.");

        return $this->errores ? self::FAILURE : self::SUCCESS;
    }

    private function procesar(ImagenService $imagenes, string $ruta, callable $actualizar): void
    {
        $disco = Storage::disk('public');

        try {
            if (! $disco->exists($ruta)) {
                throw new \RuntimeException('el archivo no existe');
            }

            if (str_ends_with($ruta, '.webp')) {
                if (! $disco->exists(ImagenService::rutaMiniatura($ruta))) {
                    $imagenes->crearMiniatura($ruta);
                    $this->convertidas++;
                }

                return;
            }

            // La original se borra solo cuando la base ya apunta a la nueva
            $nueva = $imagenes->guardarContenido($disco->get($ruta), dirname($ruta));
            $actualizar($nueva);
            $disco->delete($ruta);
            $this->convertidas++;
        } catch (Throwable $error) {
            $this->errores++;
            $this->warn("{$ruta}: {$error->getMessage()}");
        }
    }
}
