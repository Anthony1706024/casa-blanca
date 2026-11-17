<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImagenService
{
    private const ANCHO_MAXIMO = 1200;

    private const CALIDAD = 78;

    // La miniatura es lo que carga la lista de la carta; la imagen grande solo se pide al abrir el modal
    private const ANCHO_MINIATURA = 240;

    private const CALIDAD_MINIATURA = 70;

    // ==========================================
    // GUARDAR UNA IMAGEN SUBIDA: devuelve su ruta en el disco public
    // ==========================================
    public function guardar(UploadedFile $archivo, string $carpeta, string $campo = 'imagen'): string
    {
        return $this->guardarContenido($archivo->get(), $carpeta, $campo);
    }

    public function guardarContenido(string $contenido, string $carpeta, string $campo = 'imagen'): string
    {
        // Si el servidor no tiene WebP se guarda la original: mejor lenta que perder la foto
        if (! self::soportaWebp()) {
            Log::warning('GD sin soporte WebP: la imagen se guarda en su formato original.');

            $tipo = getimagesizefromstring($contenido)['mime'] ?? 'image/jpeg';
            $ruta = $carpeta.'/'.Str::uuid().'.'.Str::after($tipo, '/');
            Storage::disk('public')->put($ruta, $contenido);

            return $ruta;
        }

        $imagen = $this->abrir($contenido, $campo);
        $ruta = $carpeta.'/'.Str::uuid().'.webp';

        Storage::disk('public')->put($ruta, $this->aWebp($imagen, self::ANCHO_MAXIMO, self::CALIDAD));
        Storage::disk('public')->put(self::rutaMiniatura($ruta), $this->aWebp($imagen, self::ANCHO_MINIATURA, self::CALIDAD_MINIATURA));

        return $ruta;
    }

    public function crearMiniatura(string $ruta): void
    {
        $imagen = $this->abrir(Storage::disk('public')->get($ruta), 'imagen');

        Storage::disk('public')->put(self::rutaMiniatura($ruta), $this->aWebp($imagen, self::ANCHO_MINIATURA, self::CALIDAD_MINIATURA));
    }

    public function eliminar(?string $ruta): void
    {
        if ($ruta) {
            Storage::disk('public')->delete([$ruta, self::rutaMiniatura($ruta)]);
        }
    }

    public static function rutaMiniatura(string $ruta): string
    {
        return dirname($ruta).'/mini/'.basename($ruta);
    }

    public static function soportaWebp(): bool
    {
        return function_exists('imagewebp') && function_exists('imagecreatefromstring');
    }

    private function abrir(string $contenido, string $campo): GdImage
    {
        $datos = @getimagesizefromstring($contenido);

        if (! $datos) {
            throw ValidationException::withMessages([$campo => 'El archivo no es una imagen válida.']);
        }

        // Una foto de muy alta resolución pesa poco en disco pero ocupa mucha memoria al abrirla
        $limite = ini_parse_quantity(ini_get('memory_limit'));
        if ($limite > 0 && $limite - memory_get_usage() < $datos[0] * $datos[1] * 5) {
            throw ValidationException::withMessages([$campo => 'La imagen tiene demasiada resolución. Redúcela e inténtalo de nuevo.']);
        }

        $imagen = @imagecreatefromstring($contenido);

        if (! $imagen) {
            throw ValidationException::withMessages([$campo => 'No se pudo procesar la imagen.']);
        }

        imagepalettetotruecolor($imagen);

        return $this->enderezar($imagen, $contenido, $datos['mime']);
    }

    // Las fotos de celular guardan la rotación en EXIF y WebP la perdería
    private function enderezar(GdImage $imagen, string $contenido, string $tipo): GdImage
    {
        if ($tipo !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $imagen;
        }

        $exif = @exif_read_data('data://image/jpeg;base64,'.base64_encode($contenido));

        $angulo = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;

        return $angulo ? imagerotate($imagen, $angulo, 0) : $imagen;
    }

    private function aWebp(GdImage $imagen, int $anchoMaximo, int $calidad): string
    {
        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);
        $anchoFinal = min($ancho, $anchoMaximo);
        $altoFinal = max(1, (int) round($alto * $anchoFinal / $ancho));

        // Lienzo transparente para no perder el fondo de los PNG
        $lienzo = imagecreatetruecolor($anchoFinal, $altoFinal);
        imagealphablending($lienzo, false);
        imagesavealpha($lienzo, true);
        imagefill($lienzo, 0, 0, imagecolorallocatealpha($lienzo, 0, 0, 0, 127));
        imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $anchoFinal, $altoFinal, $ancho, $alto);

        ob_start();
        imagewebp($lienzo, null, $calidad);

        return ob_get_clean();
    }
}
