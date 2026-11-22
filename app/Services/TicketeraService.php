<?php

namespace App\Services;

use App\Models\Contenido;
use App\Models\Impresion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Recibo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

// Arma el voucher de cobro y los tickets de cocina en comandos ESC/POS y los manda a la ticketera.
// TICKETERA_MODO=cola (por defecto): se dejan en la cola `impresiones` y el agente de la PC de caja los imprime;
//   funciona igual en local y en Railway. Requiere TICKETERA_CLAVE.
// TICKETERA_MODO=directo: se imprimen en esta misma PC con imprimir-raw.ps1. Requiere TICKETERA_IMPRESORA.
// Sin configurar, no se imprime nada aquí y las vistas usan el diálogo de impresión del navegador.
class TicketeraService
{
    public const COLA = 'cola';

    public const DIRECTO = 'directo';

    private const INICIAR = "\x1B\x40";

    private const TABLA_850 = "\x1B\x74\x02";     // caracteres con tildes y ñ (página de códigos 850)

    private const CENTRO = "\x1B\x61\x01";

    private const IZQUIERDA = "\x1B\x61\x00";

    private const NEGRITA = "\x1B\x45\x01";

    private const SIN_NEGRITA = "\x1B\x45\x00";

    private const GRANDE = "\x1D\x21\x11";        // doble alto y ancho

    private const ALTO = "\x1D\x21\x01";          // doble alto, mismo ancho

    private const NORMAL = "\x1D\x21\x00";

    private const CORTAR = "\x1D\x56\x42\x00";    // avanza y corta el papel

    private int $columnas = 48;

    private string $datos = '';

    public function modo(): string
    {
        return config('services.ticketera.modo') === self::DIRECTO ? self::DIRECTO : self::COLA;
    }

    public function enCola(): bool
    {
        return $this->modo() === self::COLA;
    }

    public function configurada(): bool
    {
        return $this->enCola()
            ? filled(config('services.ticketera.clave'))
            : filled(config('services.ticketera.impresora'));
    }

    // Devuelve true si el voucher quedó en la cola (modo cola) o llegó completo a la impresora (modo directo)
    public function imprimirVoucher(Recibo $recibo): bool
    {
        $recibo->loadMissing('pedido');

        return $this->configurada() && $this->enviar(
            $this->voucher($recibo),
            Impresion::VOUCHER,
            "Voucher {$recibo->numero} – {$recibo->pedido->numero}",
            $recibo->id,
            $recibo->pedido_id,
        );
    }

    // Tickets de cocina: uno por cocina, cada uno con su corte de papel, todos en un solo trabajo. $lote viene de ComandaService::lote
    public function imprimirComanda(Pedido $pedido, array $lote): bool
    {
        $cocinas = collect(array_keys($lote['tickets']))->map(fn ($cocina) => Producto::COCINAS[$cocina] ?? $cocina)->implode(', ');
        $descripcion = "Comanda {$pedido->numero} – {$cocinas}".($lote['reimpresion'] ? ' (reimpresión)' : '');

        return $this->configurada() && $this->enviar($this->comanda($pedido, $lote), Impresion::COMANDA, $descripcion, null, $pedido->id);
    }

    // Arma los bytes de los tickets de cocina. Público para poder revisarlo en pruebas.
    public function comanda(Pedido $pedido, array $lote): string
    {
        $pedido->loadMissing('mozo');
        $negocio = Contenido::valores();
        $ahora = now();
        $hora = $ahora->format('h:i').($ahora->format('A') === 'AM' ? ' a. m.' : ' p. m.');
        $total = count($lote['tickets']);
        $numero = 0;

        $this->columnas = $negocio['ticket_ancho'] === '58' ? 32 : 48;
        $this->datos = '';

        foreach ($lote['tickets'] as $cocina => $lineas) {
            $numero++;
            $this->comando(self::INICIAR.self::TABLA_850);

            // Cocina y mesa en grande: es lo primero que se lee
            $this->comando(self::CENTRO.self::NEGRITA.self::GRANDE);
            $this->linea(mb_strtoupper(Producto::COCINAS[$cocina] ?? $cocina));
            $this->comando(self::NORMAL);
            if ($lote['adicional']) {
                $this->linea('*** ADICIONAL ***');
            }
            if ($lote['reimpresion']) {
                $this->linea('(REIMPRESIÓN)');
            }
            $this->comando(self::SIN_NEGRITA);
            $this->linea("Ticket {$numero} de {$total}");
            $this->comando(self::IZQUIERDA);
            $this->raya('=');

            $this->comando(self::CENTRO.self::NEGRITA.self::GRANDE);
            $this->linea($pedido->mesa ? 'MESA '.$pedido->mesa : 'SIN MESA');
            $this->comando(self::NORMAL.self::SIN_NEGRITA.self::IZQUIERDA);
            $this->dosLados('Pedido: '.$pedido->numero, $ahora->format('d/m').' '.$hora);
            if ($pedido->cliente_nombre) {
                $this->linea('Cliente: '.$pedido->cliente_nombre);
            }
            $this->linea('Atiende: '.($pedido->mozo ? Str::before($pedido->mozo->name.' ', ' ') : 'Pedido del cliente'));
            $this->raya();

            // Productos: cantidad y nombre en doble alto para leerlos de lejos
            $this->comando(self::NEGRITA.self::ALTO);
            foreach ($lineas as $linea) {
                $cantidad = str_pad($linea['cantidad'], 2, ' ', STR_PAD_LEFT).' x ';
                $partes = explode("\n", wordwrap($linea['nombre'], $this->columnas - mb_strlen($cantidad), "\n", true));
                $this->linea($cantidad.array_shift($partes));
                foreach ($partes as $parte) {
                    $this->linea(str_repeat(' ', mb_strlen($cantidad)).$parte);
                }
            }
            $this->comando(self::NORMAL.self::SIN_NEGRITA);
            $this->raya();

            if ($pedido->observacion) {
                $this->negrita('OBSERVACIÓN:');
                $this->linea(wordwrap($pedido->observacion, $this->columnas, "\n", true));
                $this->raya();
            }

            $this->linea("\n\n\n");
            $this->comando(self::CORTAR);
        }

        return $this->datos;
    }

    // Modo cola: deja el trabajo para el agente. Modo directo: lo manda en RAW y devuelve true si llegó completo.
    private function enviar(string $bytes, string $tipo, string $descripcion, ?int $reciboId, ?int $pedidoId): bool
    {
        if ($this->enCola()) {
            app(ColaImpresionService::class)->encolar($bytes, $tipo, $descripcion, $reciboId, $pedidoId);

            return true;
        }

        $archivo = tempnam(sys_get_temp_dir(), 'ticket');
        file_put_contents($archivo, $bytes);

        try {
            // `php artisan serve` quita las variables de Windows y sin SystemRoot PowerShell no arranca (error 8009001d)
            $windows = getenv('SystemRoot') ?: 'C:\\Windows';

            $resultado = Process::timeout(30)->env([
                'SystemRoot' => $windows,
                'windir' => $windows,
                'TEMP' => sys_get_temp_dir(),
                'TMP' => sys_get_temp_dir(),
            ])->run([
                $windows.'\\System32\\WindowsPowerShell\\v1.0\\powershell.exe', '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass',
                '-File', resource_path('ticketera/imprimir-raw.ps1'),
                '-Impresora', config('services.ticketera.impresora'),
                '-Archivo', $archivo,
            ]);

            if (! $resultado->successful()) {
                Log::warning('No se pudo imprimir: '.$descripcion, ['error' => $resultado->errorOutput()]);
            }

            return $resultado->successful();
        } finally {
            @unlink($archivo);
        }
    }

    // Arma los bytes del voucher. Público para poder revisarlo en pruebas.
    public function voucher(Recibo $recibo): string
    {
        $recibo->loadMissing(['pedido.items', 'pedido.mozo', 'pagos', 'cajero']);
        $negocio = Contenido::valores();
        $pedido = $recibo->pedido;
        $fecha = $recibo->created_at;
        $atendio = $pedido->mozo ?? $recibo->cajero;

        $this->columnas = $negocio['ticket_ancho'] === '58' ? 32 : 48;
        $this->datos = self::INICIAR.self::TABLA_850;

        // Encabezado: logo y datos del negocio
        $this->logo();
        $this->comando(self::CENTRO.self::NEGRITA.self::GRANDE);
        $this->linea($negocio['ticket_nombre']);
        $this->comando(self::NORMAL);
        if ($negocio['ticket_subtitulo']) {
            $this->linea($negocio['ticket_subtitulo']);
        }
        $this->comando(self::SIN_NEGRITA);
        if ($negocio['ticket_lugar']) {
            $this->linea($negocio['ticket_lugar']);
        }
        if ($negocio['ticket_ruc']) {
            $this->negrita('RUC: '.$negocio['ticket_ruc']);
        }
        $this->comando(self::IZQUIERDA);
        $this->raya();

        $this->comando(self::CENTRO);
        $this->negrita('COMPROBANTE DE CONSUMO');
        $this->linea('No válido como comprobante de pago');
        $this->negrita('N.º '.str_pad($recibo->id, 7, '0', STR_PAD_LEFT));
        $this->comando(self::IZQUIERDA);
        $this->raya();

        // Datos del pedido
        $hora = $fecha->format('h:i').($fecha->format('A') === 'AM' ? ' a. m.' : ' p. m.');
        $this->dosLados('Fecha: '.$fecha->format('d/m/Y'), 'Hora: '.$hora);
        $this->dosLados('Recibo: '.$recibo->numero, 'Pedido: '.$pedido->numero);
        if ($pedido->mesa) {
            $this->linea('Mesa: '.str_pad($pedido->mesa, 2, '0', STR_PAD_LEFT));
        }
        if ($pedido->cliente_nombre) {
            $this->linea('Cliente: '.$pedido->cliente_nombre);
        }
        $this->linea('DNI: '.$pedido->dni);
        if ($atendio) {
            $this->linea('Atendido por: '.Str::before($atendio->name.' ', ' '));
        }
        $this->raya();

        // Productos
        $this->comando(self::NEGRITA);
        $this->dosLados('Cant. Descripción', 'Importe');
        $this->comando(self::SIN_NEGRITA);
        $this->raya();
        foreach ($pedido->items as $item) {
            $importe = number_format($item->subtotal, 2);
            $texto = str_pad($item->cantidad, 2, '0', STR_PAD_LEFT).'    ';
            // El nombre se corta en varias líneas si no cabe junto al importe
            $espacio = $this->columnas - mb_strlen($texto) - mb_strlen($importe) - 1;
            $partes = explode("\n", wordwrap($item->producto_nombre, $espacio, "\n", true));
            $this->dosLados($texto.array_shift($partes), $importe);
            foreach ($partes as $parte) {
                $this->linea('      '.$parte);
            }
            if ($item->cantidad > 1) {
                $this->linea('      S/ '.number_format($item->precio_unitario, 2).' c/u');
            }
        }
        $this->raya('=');

        // Total
        $this->comando(self::CENTRO.self::NEGRITA);
        $this->linea('TOTAL A PAGAR');
        $this->comando(self::GRANDE);
        $this->linea('S/ '.number_format($recibo->total, 2));
        $this->comando(self::NORMAL.self::SIN_NEGRITA.self::IZQUIERDA);
        $this->raya('=');

        // Pagos
        foreach ($recibo->pagos as $pago) {
            $metodo = 'Método de pago: '.$pago->metodo_nombre;
            $recibo->pagos->count() > 1
                ? $this->dosLados($metodo, 'S/ '.number_format($pago->monto, 2))
                : $this->linea($metodo);
            if ($pago->numero_operacion) {
                $this->linea('N.º de operación: '.$pago->numero_operacion);
            }
        }
        $this->linea('Estado: PAGADO');
        $this->raya();

        // Despedida
        $this->comando(self::CENTRO);
        $this->negrita('¡GRACIAS POR SU VISITA!');
        if ($negocio['ticket_mensaje']) {
            $this->linea($negocio['ticket_mensaje']);
        }
        $this->linea('');
        $this->linea('Restaurante Campestre '.Str::title(mb_strtolower($negocio['ticket_nombre'])));
        $this->linea("\n\n\n");
        $this->comando(self::CORTAR);

        return $this->datos;
    }

    private function comando(string $bytes): void
    {
        $this->datos .= $bytes;
    }

    // Logo centrado en 1 bit (comando GS v 0). Se genera con resources/ticketera/generar-logo.ps1
    private function logo(): void
    {
        $archivo = resource_path('ticketera/logo.bin');
        if (is_file($archivo)) {
            $this->comando(self::CENTRO.file_get_contents($archivo)."\n".self::IZQUIERDA);
        }
    }

    private function linea(string $texto): void
    {
        // Rayas largas (–) no existen en la tabla 850
        $texto = str_replace(['–', '—'], '-', $texto);
        $this->datos .= mb_convert_encoding($texto, 'CP850', 'UTF-8')."\n";
    }

    private function negrita(string $texto): void
    {
        $this->comando(self::NEGRITA);
        $this->linea($texto);
        $this->comando(self::SIN_NEGRITA);
    }

    private function raya(string $caracter = '-'): void
    {
        $this->linea(str_repeat($caracter, $this->columnas));
    }

    private function dosLados(string $izquierda, string $derecha): void
    {
        $espacios = max(1, $this->columnas - mb_strlen($izquierda) - mb_strlen($derecha));
        $this->linea($izquierda.str_repeat(' ', $espacios).$derecha);
    }
}
