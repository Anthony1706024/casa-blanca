<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

// Copia las fotos que vienen en el repositorio (storage/app/public) al disco público
// cuando este vive en otro lado, como el volumen de Railway. Solo copia las que faltan:
// nunca pisa ni borra lo que el admin subió. Corre solo con `php artisan optimize`.
class SembrarImagenes extends Command
{
    protected $signature = 'imagenes:sembrar';

    protected $description = 'Copia al disco público las fotos del repositorio que aún no estén ahí';

    public function handle(): int
    {
        $origen = storage_path('app/public');
        $destino = rtrim((string) config('filesystems.disks.public.root'), '/\\');

        if (! is_dir($origen) || realpath($origen) === realpath($destino)) {
            $this->components->info('Nada que copiar: el disco público ya es storage/app/public.');

            return self::SUCCESS;
        }

        $copiadas = 0;
        foreach (File::allFiles($origen) as $archivo) {
            if ($archivo->getFilename() === '.gitignore') {
                continue;
            }

            $ruta = $destino.DIRECTORY_SEPARATOR.$archivo->getRelativePathname();
            if (! File::exists($ruta)) {
                File::ensureDirectoryExists(dirname($ruta));
                File::copy($archivo->getPathname(), $ruta);
                $copiadas++;
            }
        }

        $this->components->info("Fotos copiadas al disco público: {$copiadas}.");

        return self::SUCCESS;
    }
}
