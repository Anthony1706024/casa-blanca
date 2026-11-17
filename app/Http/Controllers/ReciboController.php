<?php

namespace App\Http\Controllers;

use App\Models\Recibo;

class ReciboController extends Controller
{
    public function show($id)
    {
        $recibo = Recibo::with(['pedido.items', 'pagos', 'cajero'])->findOrFail($id);

        return view('recibos.show', compact('recibo'));
    }
}
