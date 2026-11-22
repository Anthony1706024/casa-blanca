<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Tests\TestCase;

class SembrarImagenesTest extends TestCase
{
    private string $destino;

    protected function setUp(): void
    {
        parent::setUp();

        $this->destino = sys_get_temp_dir().DIRECTORY_SEPARATOR.'volumen-'.uniqid();
        config(['filesystems.disks.public.root' => $this->destino]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->destino);

        parent::tearDown();
    }

    public function test_copia_solo_las_fotos_que_faltan_sin_pisar_las_subidas(): void
    {
        $origen = storage_path('app/public');
        $primera = collect(File::allFiles($origen))->first(fn ($archivo) => $archivo->getFilename() !== '.gitignore');

        if (! $primera) {
            $this->markTestSkipped('No hay fotos en storage/app/public para probar.');
        }

        // Una foto que el admin ya cambió en el volumen no se reemplaza
        File::ensureDirectoryExists(dirname($this->destino.'/'.$primera->getRelativePathname()));
        File::put($this->destino.'/'.$primera->getRelativePathname(), 'subida por el admin');

        $this->artisan('imagenes:sembrar')->assertSuccessful();

        $this->assertSame('subida por el admin', File::get($this->destino.'/'.$primera->getRelativePathname()));
        $total = collect(File::allFiles($origen))->reject(fn ($archivo) => $archivo->getFilename() === '.gitignore')->count();
        $this->assertCount($total, File::allFiles($this->destino));

        // Correrlo otra vez no copia nada
        $this->artisan('imagenes:sembrar')->expectsOutputToContain('copiadas al disco público: 0')->assertSuccessful();
    }

    public function test_sin_volumen_no_hace_nada(): void
    {
        config(['filesystems.disks.public.root' => storage_path('app/public')]);

        $this->artisan('imagenes:sembrar')->expectsOutputToContain('Nada que copiar')->assertSuccessful();
    }

    public function test_corre_dentro_de_optimize(): void
    {
        $this->assertContains('imagenes:sembrar', ServiceProvider::$optimizeCommands);
    }
}
