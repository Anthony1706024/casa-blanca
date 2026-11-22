<?php

namespace App\Services;

use App\Models\Impresion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// ==========================================
// COLA DE IMPRESIÓN: la app deja los tickets aquí y el agente de la PC de caja
// (resources/ticketera/agente) los pide, los imprime y avisa cómo le fue.
// ==========================================
class ColaImpresionService
{
    // Tras este tiempo en "enviando" sin respuesta del agente, se vuelve a ofrecer
    public const MINUTOS_ENVIANDO = 2;

    // Errores seguidos antes de dejarla en "error" (se reintenta a mano)
    public const MAX_INTENTOS = 3;

    public function encolar(string $bytes, string $tipo, string $descripcion, ?int $reciboId = null, ?int $pedidoId = null): Impresion
    {
        return Impresion::create([
            'tipo' => $tipo,
            'recibo_id' => $reciboId,
            'pedido_id' => $pedidoId,
            'descripcion' => mb_substr($descripcion, 0, 255),
            'datos' => base64_encode($bytes),
            'estado' => Impresion::PENDIENTE,
        ]);
    }

    // Entrega las más antiguas y las marca "enviando" para no darlas dos veces
    public function tomarPendientes(int $limite = 5): Collection
    {
        return DB::transaction(function () use ($limite) {
            $impresiones = Impresion::where(function ($q) {
                $q->where('estado', Impresion::PENDIENTE)
                    ->orWhere(fn ($q) => $q->where('estado', Impresion::ENVIANDO)
                        ->where('tomada_en', '<', now()->subMinutes(self::MINUTOS_ENVIANDO)));
            })
                ->orderBy('id')
                ->limit($limite)
                ->lockForUpdate()
                ->get();

            if ($impresiones->isNotEmpty()) {
                Impresion::whereKey($impresiones->modelKeys())->update(['estado' => Impresion::ENVIANDO, 'tomada_en' => now()]);
            }

            return $impresiones;
        });
    }

    public function marcarImpresa(Impresion $impresion): void
    {
        $impresion->update(['estado' => Impresion::IMPRESA, 'impresa_en' => now(), 'error' => null]);
    }

    public function marcarError(Impresion $impresion, string $mensaje): void
    {
        $intentos = $impresion->intentos + 1;

        $impresion->update([
            'intentos' => $intentos,
            'error' => mb_substr($mensaje, 0, 1000),
            'estado' => $intentos < self::MAX_INTENTOS ? Impresion::PENDIENTE : Impresion::ERROR,
            'tomada_en' => null,
        ]);
    }

    // Desde la pantalla "Impresiones": vuelve a la cola con intentos nuevos
    public function reintentar(Impresion $impresion): void
    {
        $impresion->update(['estado' => Impresion::PENDIENTE, 'intentos' => 0, 'tomada_en' => null]);
    }

    // Pendientes que nadie recoge: el agente no está corriendo o no puede conectarse
    public function hayAtascadas(): bool
    {
        return Impresion::whereIn('estado', [Impresion::PENDIENTE, Impresion::ENVIANDO])
            ->where('created_at', '<', now()->subMinute())
            ->exists();
    }
}
