<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Nuevo mensaje al pie del voucher de cobro. Solo reemplaza el mensaje anterior por defecto:
// si el admin ya escribió otro, se respeta.
return new class extends Migration
{
    private const ANTERIOR = 'Esperamos volver a atenderlo pronto.';

    private const NUEVO = 'Los mejores momentos se comparten en familia';

    public function up(): void
    {
        DB::table('contenidos')->where('clave', 'ticket_mensaje')->where('valor', self::ANTERIOR)->update(['valor' => self::NUEVO]);
    }

    public function down(): void
    {
        DB::table('contenidos')->where('clave', 'ticket_mensaje')->where('valor', self::NUEVO)->update(['valor' => self::ANTERIOR]);
    }
};
