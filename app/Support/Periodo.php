<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

// Periodo de un reporte: día, semana (lunes a domingo), mes, año o un rango libre.
// Se lee de la URL (?periodo=mes&fecha=2026-11-22 o ?periodo=rango&desde=...&hasta=...).
class Periodo
{
    public const TIPOS = [
        'dia' => 'Día',
        'semana' => 'Semana',
        'mes' => 'Mes',
        'anio' => 'Año',
        'rango' => 'Rango',
    ];

    private const MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    public function __construct(
        public readonly string $tipo,
        public readonly Carbon $fecha,
        public readonly Carbon $inicio,
        public readonly Carbon $fin,
    ) {}

    public static function desdeRequest(Request $request): self
    {
        $tipo = array_key_exists($request->query('periodo'), self::TIPOS) ? $request->query('periodo') : 'dia';
        $fecha = self::leerFecha($request->query('fecha')) ?? now()->startOfDay();

        if ($tipo === 'rango') {
            $desde = self::leerFecha($request->query('desde')) ?? $fecha->copy();
            $hasta = self::leerFecha($request->query('hasta')) ?? $desde->copy();
            if ($hasta->lt($desde)) {
                [$desde, $hasta] = [$hasta, $desde];
            }

            return new self('rango', $desde->copy(), $desde->startOfDay(), $hasta->endOfDay());
        }

        return self::de($tipo, $fecha);
    }

    public static function de(string $tipo, Carbon $fecha): self
    {
        $fecha = $fecha->copy()->startOfDay();
        [$inicio, $fin] = match ($tipo) {
            'semana' => [$fecha->copy()->startOfWeek(Carbon::MONDAY), $fecha->copy()->endOfWeek(Carbon::SUNDAY)],
            'mes' => [$fecha->copy()->startOfMonth(), $fecha->copy()->endOfMonth()],
            'anio' => [$fecha->copy()->startOfYear(), $fecha->copy()->endOfYear()],
            default => [$fecha->copy(), $fecha->copy()->endOfDay()],
        };

        return new self($tipo, $fecha, $inicio, $fin);
    }

    // Un año o un rango largo se agrupa por mes; lo demás, por día
    public function agrupaPorMes(): bool
    {
        return $this->tipo === 'anio' || $this->inicio->diffInDays($this->fin) > 62;
    }

    public function etiqueta(): string
    {
        $i = $this->inicio;
        $f = $this->fin;

        return match ($this->tipo) {
            'dia' => ucfirst($i->locale('es')->isoFormat('dddd')).' '.$i->format('d/m/Y'),
            'semana' => 'Semana del '.$i->format('d/m').' al '.$f->format('d/m/Y'),
            'mes' => ucfirst(self::MESES[$i->month - 1]).' '.$i->year,
            'anio' => 'Año '.$i->year,
            default => 'Del '.$i->format('d/m/Y').' al '.$f->format('d/m/Y'),
        };
    }

    public static function nombreMes(Carbon $fecha): string
    {
        return ucfirst(self::MESES[$fecha->month - 1]).' '.$fecha->year;
    }

    // Parámetros de la URL para este periodo (y para el anterior o el siguiente)
    public function parametros(): array
    {
        return $this->tipo === 'rango'
            ? ['periodo' => 'rango', 'desde' => $this->inicio->toDateString(), 'hasta' => $this->fin->toDateString()]
            : ['periodo' => $this->tipo, 'fecha' => $this->fecha->toDateString()];
    }

    public function anterior(): ?self
    {
        return $this->mover(-1);
    }

    public function siguiente(): ?self
    {
        return $this->mover(1);
    }

    private function mover(int $paso): ?self
    {
        $fecha = match ($this->tipo) {
            'dia' => $this->fecha->copy()->addDays($paso),
            'semana' => $this->fecha->copy()->addWeeks($paso),
            'mes' => $this->fecha->copy()->startOfMonth()->addMonths($paso),
            'anio' => $this->fecha->copy()->startOfYear()->addYears($paso),
            default => null,
        };

        return $fecha ? self::de($this->tipo, $fecha) : null;
    }

    private static function leerFecha(?string $valor): ?Carbon
    {
        if (! $valor || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $valor)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
