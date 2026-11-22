<?php

namespace App\Services;

use App\Models\Egreso;
use App\Models\Pago;
use App\Models\Recibo;
use App\Support\Periodo;
use Carbon\Carbon;

// Reportes de ingresos (recibos cobrados) y egresos para un periodo.
// Los ingresos salen de recibos.total y pagos.monto, nunca del precio actual de los productos.
class FinanzasService
{
    // Totales del periodo
    public function resumen(Periodo $periodo): array
    {
        $recibos = $this->recibos($periodo);
        $ingresos = (float) (clone $recibos)->sum('total');
        $cantidad = (clone $recibos)->count();
        $egresos = (float) $this->egresos($periodo)->sum('monto');

        $porMetodo = array_fill_keys(array_keys(Pago::METODOS), 0.0);
        Pago::query()
            ->join('recibos', 'recibos.id', '=', 'pagos.recibo_id')
            ->whereBetween('recibos.created_at', [$periodo->inicio, $periodo->fin])
            ->groupBy('pagos.metodo')
            ->selectRaw('pagos.metodo as metodo, SUM(pagos.monto) as total')
            ->get()
            ->each(function ($fila) use (&$porMetodo) {
                $porMetodo[$fila->metodo] = ($porMetodo[$fila->metodo] ?? 0) + (float) $fila->total;
            });

        return [
            'ingresos' => $ingresos,
            'recibos' => $cantidad,
            'promedio' => $cantidad ? $ingresos / $cantidad : 0,
            'egresos' => $egresos,
            'balance' => $ingresos - $egresos,
            'por_metodo' => $porMetodo,
        ];
    }

    // Una fila por día (o por mes en un año): recibos, cobrado por método, ingresos, egresos y balance.
    // Solo los días con movimiento, del más reciente al más antiguo.
    public function filas(Periodo $periodo): array
    {
        $porMes = $periodo->agrupaPorMes();
        $clave = fn (string $dia) => $porMes ? substr($dia, 0, 7) : substr($dia, 0, 10);
        $filas = [];
        $fila = function (string $k) use (&$filas, $porMes) {
            return $filas[$k] ??= [
                'clave' => $k,
                'fecha' => Carbon::parse($porMes ? $k.'-01' : $k),
                'recibos' => 0,
                'metodos' => array_fill_keys(array_keys(Pago::METODOS), 0.0),
                'ingresos' => 0.0,
                'egresos' => 0.0,
            ];
        };

        $this->recibos($periodo)
            ->selectRaw('DATE(created_at) as dia, COUNT(*) as cantidad, SUM(total) as total')
            ->groupByRaw('DATE(created_at)')
            ->get()
            ->each(function ($r) use ($fila, &$filas, $clave) {
                $k = $clave((string) $r->dia);
                $fila($k);
                $filas[$k]['recibos'] += (int) $r->cantidad;
                $filas[$k]['ingresos'] += (float) $r->total;
            });

        Pago::query()
            ->join('recibos', 'recibos.id', '=', 'pagos.recibo_id')
            ->whereBetween('recibos.created_at', [$periodo->inicio, $periodo->fin])
            ->selectRaw('DATE(recibos.created_at) as dia, pagos.metodo as metodo, SUM(pagos.monto) as total')
            ->groupByRaw('DATE(recibos.created_at), pagos.metodo')
            ->get()
            ->each(function ($p) use ($fila, &$filas, $clave) {
                $k = $clave((string) $p->dia);
                $fila($k);
                $filas[$k]['metodos'][$p->metodo] = ($filas[$k]['metodos'][$p->metodo] ?? 0) + (float) $p->total;
            });

        $this->egresos($periodo)
            ->selectRaw('fecha as dia, SUM(monto) as total')
            ->groupBy('fecha')
            ->get()
            ->each(function ($e) use ($fila, &$filas, $clave) {
                $k = $clave((string) $e->getRawOriginal('dia'));
                $fila($k);
                $filas[$k]['egresos'] += (float) $e->total;
            });

        krsort($filas);

        return array_values(array_map(fn ($f) => $f + ['balance' => $f['ingresos'] - $f['egresos']], $filas));
    }

    // Total de egresos por categoría, de mayor a menor
    public function egresosPorCategoria(Periodo $periodo): array
    {
        return $this->egresos($periodo)
            ->join('categorias_egreso', 'categorias_egreso.id', '=', 'egresos.categoria_egreso_id')
            ->groupBy('categorias_egreso.id', 'categorias_egreso.nombre')
            ->selectRaw('categorias_egreso.id as id, categorias_egreso.nombre as nombre, COUNT(*) as cantidad, SUM(egresos.monto) as total')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($c) => ['id' => $c->id, 'nombre' => $c->nombre, 'cantidad' => (int) $c->cantidad, 'total' => (float) $c->total])
            ->all();
    }

    public function recibos(Periodo $periodo)
    {
        return Recibo::query()->whereBetween('created_at', [$periodo->inicio, $periodo->fin]);
    }

    public function egresos(Periodo $periodo)
    {
        return Egreso::query()->whereBetween('egresos.fecha', [$periodo->inicio->toDateString(), $periodo->fin->toDateString()]);
    }
}
