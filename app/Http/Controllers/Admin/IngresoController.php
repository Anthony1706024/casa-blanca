<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\SaldoInicial;
use App\Services\FinanzasService;
use App\Support\Periodo;
use Carbon\Carbon;
use Illuminate\Http\Request;

// Ingresos diarios: lo cobrado por caja en el periodo, por día (o por mes), con egresos y balance
class IngresoController extends Controller
{
    public function __construct(private FinanzasService $finanzas) {}

    public function index(Request $request)
    {
        $periodo = Periodo::desdeRequest($request);

        // El formulario propone el día que se está viendo (o hoy, en semana, mes y año)
        $fechaSaldo = $periodo->tipo === 'dia' && $periodo->fecha->lte(now()) ? $periodo->fecha : now()->startOfDay();

        return view('admin.ingresos.index', [
            'periodo' => $periodo,
            'fechaSaldo' => $fechaSaldo,
            'saldoDelDia' => SaldoInicial::where('fecha', $fechaSaldo->toDateString())->first(),
            'saldos' => $this->finanzas->saldos($periodo)->with('usuario')->orderByDesc('fecha')->get(),
            'resumen' => $this->finanzas->resumen($periodo),
            'filas' => $this->finanzas->filas($periodo),
            'metodos' => Pago::METODOS,
        ]);
    }

    // Registra o cambia el saldo inicial de un día (uno por fecha)
    public function saldoGuardar(Request $request)
    {
        $datos = $request->validate([
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'monto' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'nota' => ['nullable', 'string', 'max:255'],
        ], [], ['monto' => 'saldo inicial']);

        $fecha = Carbon::parse($datos['fecha'])->toDateString();
        $saldo = SaldoInicial::where('fecha', $fecha)->first() ?? new SaldoInicial(['fecha' => $fecha]);
        $saldo->fill(['monto' => $datos['monto'], 'nota' => $datos['nota'] ?? null, 'user_id' => $request->user()->id])->save();

        return redirect()->route('admin.ingresos.index', ['periodo' => 'dia', 'fecha' => $fecha])
            ->with('success', 'Saldo inicial del '.$saldo->fecha->format('d/m/Y').': S/ '.number_format($saldo->monto, 2).'.');
    }

    public function saldoEliminar($id)
    {
        SaldoInicial::findOrFail($id)->delete();

        return back()->with('success', 'Saldo inicial eliminado.');
    }

    // Reporte para Excel (CSV con ; y BOM, así abre bien con tildes)
    public function exportar(Request $request)
    {
        $periodo = Periodo::desdeRequest($request);
        $filas = $this->finanzas->filas($periodo);
        $resumen = $this->finanzas->resumen($periodo);
        $porMes = $periodo->agrupaPorMes();

        $encabezado = array_merge([$porMes ? 'Mes' : 'Fecha', 'Saldo inicial', 'Recibos'], array_values(Pago::METODOS), ['Ingresos', 'Egresos', 'Balance', 'Saldo final']);
        $lineas = [];
        foreach ($filas as $fila) {
            $lineas[] = array_merge(
                [
                    $porMes ? Periodo::nombreMes($fila['fecha']) : $fila['fecha']->format('d/m/Y'),
                    $fila['saldo_inicial'] !== null ? self::numero($fila['saldo_inicial']) : '',
                    $fila['recibos'],
                ],
                array_map(fn ($monto) => self::numero($monto), array_values($fila['metodos'])),
                [
                    self::numero($fila['ingresos']), self::numero($fila['egresos']), self::numero($fila['balance']),
                    $fila['saldo_final'] !== null ? self::numero($fila['saldo_final']) : '',
                ],
            );
        }
        $lineas[] = array_merge(
            ['TOTAL', $resumen['saldo_inicial'] !== null ? self::numero($resumen['saldo_inicial']) : '', $resumen['recibos']],
            array_map(fn ($monto) => self::numero($monto), array_values($resumen['por_metodo'])),
            [self::numero($resumen['ingresos']), self::numero($resumen['egresos']), self::numero($resumen['balance']), self::numero($resumen['saldo_final'])],
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
