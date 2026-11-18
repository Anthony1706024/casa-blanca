<?php

namespace App\Http\Controllers;

use App\Models\Contenido;
use App\Models\Producto;
use App\Models\SitioItem;

class InicioController extends Controller
{
    public function index()
    {
        $contenido = Contenido::valores();
        $instalaciones = SitioItem::publicados('instalacion');
        $galeria = SitioItem::publicados('galeria');
        $testimonios = SitioItem::publicados('testimonio');
        $actividades = SitioItem::publicados('actividad');
        $eventos = SitioItem::publicados('evento');

        // Precios reales de la carta, los mismos que se cobran
        $destacados = Producto::with('insumos')
            ->where('destacado', true)
            ->whereNotNull('precio_venta')
            ->orderBy('nombre')
            ->limit(8)
            ->get();

        return view('welcome', compact('contenido', 'instalaciones', 'galeria', 'testimonios', 'actividades', 'eventos', 'destacados'));
    }
}
