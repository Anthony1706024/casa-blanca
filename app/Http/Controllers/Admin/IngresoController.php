<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Services\FinanzasService;
use App\Support\Periodo;
use Illuminate\Http\Request;

// Ingresos diarios: lo cobrado por caja en el periodo, por día (o por mes), con egresos y balance
class IngresoController extends Controller
{
    public function __construct(private FinanzasService $finanzas) {}

    public function index(Request $request)
    {
        $periodo = Periodo::desdeRequest($request);

        return view('admin.ingresos.index', [
            'periodo' => $periodo,
            'resumen' => $this->finanzas->resumen($periodo),
            'filas' => $this->finanzas->filas($periodo),
            'metodos' => Pago::METODOS,
        ]);
    }

    // Reporte para Excel (CSV con ; y BOM, así abre bien con tildes)
    public function exportar(Request $request)
    {
        $periodo = Periodo::desdeRequest($request);
        $filas = $this->finanzas->filas($periodo);
        $resumen = $this->finanzas->resumen($periodo);
        $porMes = $periodo->agrupaPorMes();

        $encabezado = array_merge([$porMes ? 'Mes' : 'Fecha', 'Recibos'], array_values(Pago::METODOS), ['Ingresos', 'Egresos', 'Balance']);
        $lineas = [];
        foreach ($filas as $fila) {
            $lineas[] = array_merge(
                [$porMes ? Periodo::nombreMes($fila['fecha']) : $fila['fecha']->format('d/m/Y'), $fila['recibos']],
                array_map(fn ($monto) => self::numero($monto), array_values($fila['metodos'])),
                [self::numero($fila['ingresos']), self::numero($fila['egresos']), self::numero($fila['balance'])],
            );
        }
        $lineas[] = array_merge(
            ['TOTAL', $resumen['recibos']],
            array_map(fn ($monto) => self::numero($monto), array_values($resumen['por_metodo'])),
            [self::numero($resumen['ingresos']), self::numero($resumen['egresos']), self::numero($resumen['balance'])],
        );

        return self::csv('ingresos-'.$periodo->inicio->toDateString().'-al-'.$periodo->fin->toDateString().'.csv', $periodo->etiqueta(), $encabezado, $lineas);
    }

    public static function numero(float $monto): string
    {
        return number_format($monto, 2, '.', '');
    }

    public static function csv(string $archivo, string $titulo, array $encabezado, array $lineas)
    {
        return response()->streamDownload(function () use ($titulo, $encabezado, $lineas) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Casa Blanca - '.$titulo], ';');
            fputcsv($salida, $encabezado, ';');
            foreach ($lineas as $linea) {
                fputcsv($salida, $linea, ';');
            }
            fclose($salida);
        }, $archivo, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
