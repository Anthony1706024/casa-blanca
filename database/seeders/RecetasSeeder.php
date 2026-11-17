<?php

namespace Database\Seeders;

use App\Models\CategoriaInsumo;
use App\Models\Insumo;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Crea los insumos y las recetas de la carta. Las cantidades son estimadas: cada restaurante debe ajustarlas.
// Se puede ejecutar varias veces: no cambia el stock de insumos existentes ni las recetas ya registradas.
class RecetasSeeder extends Seeder
{
    // Stock inicial de relleno: alcanza para esta cantidad de veces la mayor porción usada del insumo
    private const VECES_STOCK_INICIAL = 50;

    private const VECES_STOCK_MINIMO = 10;

    // nombre => [categoría, unidad, el cliente lo ve]
    private const INSUMOS = [
        'Pescado fresco' => ['Pescados y mariscos', 'g', true],
        'Trucha' => ['Pescados y mariscos', 'g', true],
        'Tilapia' => ['Pescados y mariscos', 'g', true],
        'Camarón' => ['Pescados y mariscos', 'g', true],
        'Langostino' => ['Pescados y mariscos', 'g', true],
        'Mariscos surtidos' => ['Pescados y mariscos', 'g', true],

        'Pollo' => ['Carnes y aves', 'g', true],
        'Pato' => ['Carnes y aves', 'presa', true],
        'Cabrito' => ['Carnes y aves', 'g', true],
        'Bistec de res' => ['Carnes y aves', 'g', true],
        'Lomo de res' => ['Carnes y aves', 'g', true],
        'Carne de res' => ['Carnes y aves', 'g', true],
        'Cerdo' => ['Carnes y aves', 'g', true],
        'Chuleta de cerdo' => ['Carnes y aves', 'g', true],
        'Cecina de res' => ['Carnes y aves', 'g', true],
        'Cecina de cerdo' => ['Carnes y aves', 'g', true],
        'Cuy' => ['Carnes y aves', 'unidad', true],
        'Jamón otuzcano' => ['Carnes y aves', 'g', true],
        'Jamón' => ['Carnes y aves', 'g', true],

        'Cebolla' => ['Verduras y tubérculos', 'g', true],
        'Tomate' => ['Verduras y tubérculos', 'g', true],
        'Lechuga' => ['Verduras y tubérculos', 'g', true],
        'Zanahoria' => ['Verduras y tubérculos', 'g', true],
        'Arveja' => ['Verduras y tubérculos', 'g', true],
        'Pimiento' => ['Verduras y tubérculos', 'g', true],
        'Espinaca' => ['Verduras y tubérculos', 'g', false],
        'Papa' => ['Verduras y tubérculos', 'g', true],
        'Papa amarilla' => ['Verduras y tubérculos', 'g', true],
        'Yuca' => ['Verduras y tubérculos', 'g', true],
        'Camote' => ['Verduras y tubérculos', 'g', true],
        'Choclo' => ['Verduras y tubérculos', 'g', true],
        'Plátano' => ['Verduras y tubérculos', 'unidad', true],

        'Limón' => ['Frutas', 'unidad', false],
        'Piña Golden' => ['Frutas', 'g', true],
        'Piña Golden entera' => ['Frutas', 'unidad', false],
        'Maracuyá' => ['Frutas', 'g', true],
        'Guanábana' => ['Frutas', 'g', true],
        'Carambola' => ['Frutas', 'g', true],
        'Guayaba' => ['Frutas', 'g', true],
        'Fresa' => ['Frutas', 'g', true],
        'Arándano' => ['Frutas', 'g', true],
        'Naranja' => ['Frutas', 'g', true],
        'Manzana' => ['Frutas', 'g', true],
        'Palta Hass' => ['Frutas', 'unidad', false],
        'Palta Fuerte' => ['Frutas', 'unidad', true],
        'Palta Choquette' => ['Frutas', 'unidad', false],

        'Arroz' => ['Abarrotes', 'g', true],
        'Trigo' => ['Abarrotes', 'g', true],
        'Frejol' => ['Abarrotes', 'g', true],
        'Tallarín' => ['Abarrotes', 'g', true],
        'Fetuccini' => ['Abarrotes', 'g', true],
        'Spaghetti' => ['Abarrotes', 'g', true],
        'Harina' => ['Abarrotes', 'g', false],
        'Galleta de soda' => ['Abarrotes', 'g', false],
        'Maní' => ['Abarrotes', 'g', false],
        'Maíz morado' => ['Abarrotes', 'g', true],
        'Azúcar' => ['Abarrotes', 'g', false],
        'Masa wantán' => ['Abarrotes', 'unidad', false],
        'Gelatina en polvo' => ['Abarrotes', 'g', true],
        'Aceite' => ['Abarrotes', 'ml', false],

        'Queso fresco' => ['Lácteos y huevos', 'g', true],
        'Leche evaporada' => ['Lácteos y huevos', 'ml', false],
        'Crema de leche' => ['Lácteos y huevos', 'ml', false],
        'Mantequilla' => ['Lácteos y huevos', 'g', false],
        'Huevo' => ['Lácteos y huevos', 'unidad', true],

        'Ajo' => ['Condimentos', 'g', false],
        'Sal' => ['Condimentos', 'g', false],
        'Ají amarillo' => ['Condimentos', 'g', false],
        'Ají panca' => ['Condimentos', 'g', false],
        'Rocoto' => ['Condimentos', 'g', false],
        'Culantro' => ['Condimentos', 'g', false],
        'Albahaca' => ['Condimentos', 'g', false],
        'Huacatay' => ['Condimentos', 'g', false],
        'Hierba buena' => ['Condimentos', 'g', false],
        'Canela' => ['Condimentos', 'g', false],
        'Sillao' => ['Condimentos', 'ml', false],
        'Vinagre' => ['Condimentos', 'ml', false],
        'Mayonesa' => ['Condimentos', 'g', false],
        'Aceituna' => ['Condimentos', 'g', true],
        'Chicha de jora' => ['Condimentos', 'ml', false],

        'Pisco' => ['Licores', 'ml', true],
        'Ron' => ['Licores', 'ml', true],
        'Vodka' => ['Licores', 'ml', true],
        'Vino tinto' => ['Licores', 'ml', true],
        'Licor de durazno' => ['Licores', 'ml', true],
        'Curazao azul' => ['Licores', 'ml', true],
        'Crema de menta' => ['Licores', 'ml', true],
        'Granadina' => ['Licores', 'ml', false],
        'Jarabe de goma' => ['Licores', 'ml', false],
        'Crema de coco' => ['Licores', 'ml', true],
        'Hielo' => ['Licores', 'g', false],
        'Ginger ale' => ['Licores', 'ml', true],
        'Coca Cola para coctel' => ['Licores', 'ml', true],
        'Agua con gas' => ['Licores', 'ml', false],
    ];

    // Productos que se compran listos: cada venta descuenta 1 unidad del insumo con el mismo nombre
    private const REVENTA = [
        'Bebidas' => [
            'Inca Kola personal', 'Coca Cola personal', 'Agua personal', 'Agua grande', 'Gordita',
            'Inca Kola 1L', 'Coca Cola 1L', 'Inca Kola 1.5L', 'Inca Kola 2L', 'Coca Cola 2L', 'Inca Kola 3L', 'Coca Cola 3L',
        ],
        'Vinos y destilados' => ['Destilado de piña Golden', 'Vino Borgoña Santiago Queirolo', 'Vino Rosé Santiago Queirolo'],
        'Cervezas' => ['Pilsen Trujillo', 'Pilsen Callao', 'Cusqueña de trigo', 'Cusqueña negra', 'Pilsen Callao en lata', 'Heineken en lata', 'Corona', 'Wild', 'Hit'],
    ];

    public function run()
    {
        $recetas = $this->recetas();

        foreach (self::REVENTA as $nombres) {
            foreach ($nombres as $nombre) {
                $recetas[$nombre] = [$nombre => 1];
            }
        }

        $recetas['Palta Hass'] = ['Palta Hass' => 1];
        $recetas['Palta Fuerte'] = ['Palta Fuerte' => [1, false]];
        $recetas['Palta Choquette'] = ['Palta Choquette' => 1];
        $recetas['Piña Golden entera para llevar'] = ['Piña Golden entera' => 1];

        DB::transaction(function () use ($recetas) {
            $insumos = $this->crearInsumos($recetas);

            foreach ($recetas as $nombre => $receta) {
                $producto = Producto::with('insumos')->where('nombre', $nombre)->first();

                if (! $producto) {
                    $this->command?->warn("Sin producto: {$nombre}");

                    continue;
                }

                // Una receta ya registrada pudo haber sido ajustada por el administrador
                if ($producto->insumos->isNotEmpty()) {
                    continue;
                }

                $lineas = [];
                foreach ($receta as $insumo => $valor) {
                    [$cantidad, $visible] = is_array($valor) ? $valor : [$valor, self::INSUMOS[$insumo][2] ?? false];
                    $lineas[$insumos[$insumo]->id] = ['cantidad' => $cantidad, 'visible_cliente' => $visible];
                }

                $producto->update(['tipo' => Producto::PLATO, 'stock' => 0]);
                $producto->insumos()->sync($lineas);
            }
        });

        $this->command?->info(count($recetas).' recetas procesadas.');
    }

    private function crearInsumos(array $recetas): array
    {
        $mayor = [];
        foreach ($recetas as $receta) {
            foreach ($receta as $insumo => $valor) {
                $mayor[$insumo] = max($mayor[$insumo] ?? 0, is_array($valor) ? $valor[0] : $valor);
            }
        }

        $reventa = array_merge(...array_map(
            fn ($categoria, $nombres) => array_fill_keys($nombres, [$categoria, 'unidad', false]),
            array_keys(self::REVENTA),
            self::REVENTA
        ));
        $definiciones = self::INSUMOS + $reventa;

        $insumos = [];
        foreach ($mayor as $nombre => $cantidad) {
            [$categoria, $unidad] = $definiciones[$nombre];

            $insumos[$nombre] = Insumo::firstOrCreate(['nombre' => $nombre], [
                'categoria_insumo_id' => CategoriaInsumo::firstOrCreate(['nombre' => $categoria])->id,
                'unidad' => $unidad,
                'stock' => ceil($cantidad * self::VECES_STOCK_INICIAL),
                'stock_minimo' => ceil($cantidad * self::VECES_STOCK_MINIMO),
            ]);
        }

        return $insumos;
    }

    // Multiplica una receta personal para sus fuentes
    private function por(array $receta, float $factor): array
    {
        return array_map(fn ($cantidad) => $cantidad * $factor, $receta);
    }

    private function recetas(): array
    {
        $acompanaCeviche = ['Cebolla' => 80, 'Limón' => 6, 'Rocoto' => 5, 'Culantro' => 3, 'Sal' => 3, 'Camote' => 100, 'Choclo' => 80, 'Lechuga' => 20];
        $cevichePescado = ['Pescado fresco' => 250] + $acompanaCeviche;
        $cevicheCamaron = ['Camarón' => 250] + $acompanaCeviche;
        $cevicheMixto = ['Pescado fresco' => 150, 'Mariscos surtidos' => 120] + $acompanaCeviche;

        $picante = ['Camarón' => 200, 'Papa' => 200, 'Arroz' => 150, 'Cebolla' => 50, 'Queso fresco' => 30, 'Ají panca' => 20, 'Leche evaporada' => 40, 'Ajo' => 5, 'Aceite' => 15];
        $alAjo = ['Camarón' => 250, 'Arroz' => 150, 'Papa' => 150, 'Ajo' => 15, 'Mantequilla' => 15, 'Aceite' => 15, 'Sal' => 2];
        $enSalsa = ['Camarón' => 250, 'Arroz' => 150, 'Papa' => 100, 'Crema de leche' => 50, 'Mantequilla' => 15, 'Sal' => 2];
        $conPina = $enSalsa + ['Piña Golden' => 80];
        $conArandano = $enSalsa + ['Arándano' => 60];
        $conMaracuya = $enSalsa + ['Maracuyá' => 60];
        $tallarinesVerdes = ['Tallarín' => 150, 'Espinaca' => 80, 'Albahaca' => 30, 'Queso fresco' => 40, 'Leche evaporada' => 40, 'Aceite' => 15, 'Ajo' => 3];

        $ajiCuy = ['Papa' => 200, 'Lechuga' => 30, 'Aceite' => 100, 'Ají panca' => 15, 'Ajo' => 5, 'Sal' => 3];
        $cuyFrito = ['Cuy' => 1] + $ajiCuy;
        $cuyGuisado = ['Cuy' => 1, 'Papa' => 200, 'Arroz' => 150, 'Ají panca' => 20, 'Maní' => 15, 'Cebolla' => 40, 'Ajo' => 5, 'Aceite' => 20];
        $chicharronCuy = ['Cuy' => 1, 'Yuca' => 200, 'Cebolla' => 50, 'Harina' => 30, 'Aceite' => 100, 'Limón' => 1, 'Sal' => 3];
        $frito = ['Cerdo' => 300, 'Yuca' => 200, 'Cebolla' => 50, 'Limón' => 1, 'Ajo' => 5, 'Aceite' => 40, 'Sal' => 3];

        $saltado = ['Cebolla' => 80, 'Tomate' => 80, 'Papa' => 200, 'Arroz' => 150, 'Ají amarillo' => 10, 'Sillao' => 15, 'Vinagre' => 10, 'Culantro' => 3, 'Ajo' => 3, 'Aceite' => 60];
        $conEnsalada = ['Arroz' => 150, 'Papa' => 150, 'Lechuga' => 30, 'Tomate' => 30, 'Aceite' => 20, 'Sal' => 3];
        $pescadoFrito = ['Arroz' => 150, 'Papa' => 150, 'Lechuga' => 30, 'Tomate' => 30, 'Aceite' => 80, 'Limón' => 1, 'Sal' => 3];
        $sangria = ['Vino tinto' => 750, 'Naranja' => 150, 'Manzana' => 100, 'Limón' => 1, 'Azúcar' => 40, 'Hielo' => 200];
        $mazamorra = ['Maíz morado' => 30, 'Piña Golden' => 30, 'Azúcar' => 30, 'Harina' => 10, 'Canela' => 1];
        $arrozConLeche = ['Arroz' => 40, 'Leche evaporada' => 80, 'Azúcar' => 25, 'Canela' => 1];
        $cremolada = fn (string $fruta, array $extra = []) => [$fruta => 150, 'Azúcar' => 40, 'Hielo' => 200] + $extra;
        $jarra = fn (string $fruta, int $gramos = 400) => [$fruta => $gramos, 'Azúcar' => 60, 'Hielo' => 100];
        $frozen = fn (string $fruta, int $gramos) => [$fruta => $gramos, 'Azúcar' => 40, 'Hielo' => 300];

        return [
            // Ceviches
            'Ceviche de pescado' => $cevichePescado,
            'Ceviche de trucha' => ['Trucha' => 250] + $acompanaCeviche,
            'Ceviche de camarón' => $cevicheCamaron,
            'Ceviche mixto' => $cevicheMixto,
            'Fuente de ceviche' => $this->por($cevichePescado, 2),
            'Fuente de ceviche de camarón' => $this->por($cevicheCamaron, 2),
            'Fuente de ceviche mixto' => $this->por($cevicheMixto, 2),
            'Leche de Tigre Clásica' => ['Pescado fresco' => 60, 'Limón' => 5, 'Cebolla' => 20, 'Rocoto' => 3, 'Culantro' => 2, 'Sal' => 2],
            'Leche de Tigre Afrodisíaca' => ['Pescado fresco' => 60, 'Mariscos surtidos' => 60, 'Camarón' => 30, 'Limón' => 5, 'Cebolla' => 20, 'Rocoto' => 3, 'Culantro' => 2, 'Sal' => 2],

            // Entradas
            'Papa a la Huancaína' => ['Papa amarilla' => 250, 'Queso fresco' => 60, 'Huevo' => 1, 'Aceituna' => 10, 'Lechuga' => 20, 'Ají amarillo' => 30, 'Leche evaporada' => 50, 'Galleta de soda' => 15, 'Aceite' => 15],
            'Ocopa' => ['Papa amarilla' => 250, 'Queso fresco' => 50, 'Huevo' => 1, 'Aceituna' => 10, 'Lechuga' => 20, 'Huacatay' => 15, 'Maní' => 20, 'Ají amarillo' => 15, 'Leche evaporada' => 50, 'Galleta de soda' => 15],
            'Tequeños de pollo, jamón y queso' => ['Pollo' => 60, 'Jamón' => 40, 'Queso fresco' => 60, 'Masa wantán' => 6, 'Aceite' => 80],
            'Tequeños de carne' => ['Carne de res' => 120, 'Cebolla' => 30, 'Masa wantán' => 6, 'Aceite' => 80],
            'Palta rellena de pollo o atún' => ['Palta Fuerte' => 1, 'Pollo' => 100, 'Cebolla' => 15, 'Lechuga' => 20, 'Mayonesa' => 25],
            'Palta rellena de langostinos' => ['Palta Fuerte' => 1, 'Langostino' => 100, 'Cebolla' => 15, 'Lechuga' => 20, 'Mayonesa' => 25],
            'Aguadito de pollo' => ['Pollo' => 180, 'Arroz' => 80, 'Papa' => 100, 'Arveja' => 30, 'Zanahoria' => 40, 'Choclo' => 40, 'Culantro' => 30, 'Ají amarillo' => 10, 'Ajo' => 3],

            // Brochetas (2 unidades)
            'Brocheta de camarón/langostino' => ['Camarón' => 160, 'Pimiento' => 40, 'Cebolla' => 40, 'Papa' => 150, 'Aceite' => 15],
            'Brocheta de pollo' => ['Pollo' => 200, 'Pimiento' => 40, 'Cebolla' => 40, 'Papa' => 150, 'Aceite' => 15],
            'Brocheta mixta' => ['Pollo' => 100, 'Bistec de res' => 80, 'Camarón' => 60, 'Pimiento' => 40, 'Cebolla' => 40, 'Papa' => 150, 'Aceite' => 15],
            'Brocheta hawaiana' => ['Pollo' => 180, 'Piña Golden' => 80, 'Pimiento' => 40, 'Cebolla' => 40, 'Papa' => 150, 'Aceite' => 15],

            // Camarones
            'Picante de camarón' => $picante,
            'Fuente de picante de camarón' => $this->por($picante, 2),
            'Camarón al ajo' => $alAjo,
            'Fuente de camarón al ajo' => $this->por($alAjo, 2),
            'Camarones en salsa de piña' => $conPina,
            'Fuente de camarones en salsa de piña' => $this->por($conPina, 2),
            'Camarones en salsa de arándanos' => $conArandano,
            'Fuente de camarones en salsa de arándanos' => $this->por($conArandano, 2),
            'Camarones en salsa de maracuyá' => $conMaracuya,
            'Fuente de camarones en salsa de maracuyá' => $this->por($conMaracuya, 2),
            'Chupe de camarón' => ['Camarón' => 180, 'Papa' => 200, 'Arroz' => 40, 'Choclo' => 50, 'Queso fresco' => 30, 'Huevo' => 1, 'Leche evaporada' => 80, 'Cebolla' => 40, 'Ají panca' => 10, 'Ajo' => 5, 'Aceite' => 15],
            'Tortilla de camarón' => ['Camarón' => 200, 'Huevo' => 3, 'Cebolla' => 40, 'Tomate' => 40, 'Arroz' => 150, 'Aceite' => 30],
            'Tallarines verdes con camarones' => ['Camarón' => 200] + $tallarinesVerdes,
            'Trilogía de camarones' => ['Camarón' => 450, 'Arroz' => 200, 'Papa' => 200, 'Cebolla' => 80, 'Piña Golden' => 50, 'Crema de leche' => 40, 'Limón' => 4, 'Ajo' => 15, 'Aceite' => 50],
            'Fuente de chicharrón de camarón' => ['Camarón' => 500, 'Yuca' => 300, 'Cebolla' => 80, 'Harina' => 80, 'Aceite' => 150, 'Limón' => 4, 'Sal' => 5],

            // Otros marinos
            'Tilapia frita' => ['Tilapia' => 350] + $pescadoFrito,
            'Trucha frita' => ['Trucha' => 350] + $pescadoFrito,
            'Arroz con mariscos' => ['Arroz' => 180, 'Mariscos surtidos' => 200, 'Arveja' => 20, 'Pimiento' => 30, 'Cebolla' => 40, 'Ají panca' => 15, 'Ajo' => 5, 'Aceite' => 20],
            'Spaghetti en salsa de mariscos' => ['Spaghetti' => 150, 'Mariscos surtidos' => 200, 'Tomate' => 100, 'Crema de leche' => 50, 'Ajo' => 5, 'Aceite' => 15],
            'Chicharrón de pescado' => ['Pescado fresco' => 250, 'Yuca' => 150, 'Cebolla' => 60, 'Harina' => 40, 'Aceite' => 100, 'Limón' => 2, 'Sal' => 3],

            // Criollos
            'Cecina de res' => ['Cecina de res' => 250, 'Yuca' => 200, 'Cebolla' => 50, 'Limón' => 1, 'Aceite' => 20],
            'Cecina de cerdo' => ['Cecina de cerdo' => 250, 'Yuca' => 200, 'Cebolla' => 50, 'Limón' => 1, 'Aceite' => 20],
            'Cecina mixta' => ['Cecina de res' => 125, 'Cecina de cerdo' => 125, 'Yuca' => 200, 'Cebolla' => 50, 'Limón' => 1, 'Aceite' => 20],
            'Fuente de cecina (pequeña)' => ['Cecina de res' => 225, 'Cecina de cerdo' => 225, 'Yuca' => 350, 'Cebolla' => 80, 'Limón' => 2, 'Aceite' => 30],
            'Fuente de cecina (mediana)' => ['Cecina de res' => 275, 'Cecina de cerdo' => 275, 'Yuca' => 400, 'Cebolla' => 90, 'Limón' => 2, 'Aceite' => 35],
            'Fuente de cecina (grande)' => ['Cecina de res' => 350, 'Cecina de cerdo' => 350, 'Yuca' => 500, 'Cebolla' => 100, 'Limón' => 3, 'Aceite' => 40],
            'Cabrito a la norteña' => ['Cabrito' => 300, 'Frejol' => 100, 'Arroz' => 150, 'Yuca' => 100, 'Culantro' => 20, 'Chicha de jora' => 60, 'Ají amarillo' => 15, 'Cebolla' => 40, 'Ajo' => 5, 'Aceite' => 20],
            'Pato guisado' => ['Pato' => 1, 'Arroz' => 150, 'Papa' => 150, 'Frejol' => 100, 'Cebolla' => 40, 'Ají panca' => 15, 'Ajo' => 5, 'Aceite' => 15],
            'Arroz con pato' => ['Pato' => 1, 'Arroz' => 180, 'Arveja' => 30, 'Zanahoria' => 30, 'Cebolla' => 40, 'Culantro' => 30, 'Chicha de jora' => 50, 'Limón' => 1, 'Ajo' => 5, 'Aceite' => 15],
            'Bistec a lo pobre' => ['Bistec de res' => 200, 'Arroz' => 150, 'Papa' => 200, 'Huevo' => 1, 'Plátano' => 1, 'Aceite' => 100, 'Sal' => 3],
            'Lomo saltado' => ['Lomo de res' => 180] + $saltado,
            'Pollo saltado' => ['Pollo' => 180] + $saltado,
            'Tallarines verdes con bistec' => ['Bistec de res' => 200] + $tallarinesVerdes,
            'Tallarines verdes con pollo' => ['Pollo' => 200] + $tallarinesVerdes,
            'Fetuccini a la huancaína' => ['Fetuccini' => 150, 'Bistec de res' => 180, 'Queso fresco' => 60, 'Ají amarillo' => 30, 'Leche evaporada' => 60, 'Galleta de soda' => 10, 'Aceite' => 20],
            'Tallarines saltados' => ['Tallarín' => 150, 'Bistec de res' => 150, 'Cebolla' => 60, 'Tomate' => 60, 'Sillao' => 15, 'Vinagre' => 10, 'Ají amarillo' => 10, 'Aceite' => 30],
            'Cuy frito' => $cuyFrito,
            'Fuente de cuy frito' => $this->por($cuyFrito, 4),
            'Cuy guisado' => $cuyGuisado,
            'Fuente de cuy guisado' => $this->por($cuyGuisado, 4),
            'Chicharrón de cuy' => $chicharronCuy,
            'Fuente de chicharrón de cuy' => $this->por($chicharronCuy, 4),
            'Frito de chancho' => $frito,
            'Fuente de frito de chancho' => $this->por($frito, 2),
            'Chuleta de cerdo' => ['Chuleta de cerdo' => 250] + $conEnsalada,
            'Jamón otuzcano' => ['Jamón otuzcano' => 200, 'Papa' => 200, 'Cebolla' => 40, 'Limón' => 1, 'Aceite' => 10],
            'Pollo a la plancha' => ['Pollo' => 220] + $conEnsalada,
            'Chicharrón de pollo' => ['Pollo' => 250, 'Yuca' => 150, 'Cebolla' => 50, 'Harina' => 40, 'Aceite' => 100, 'Limón' => 1, 'Sal' => 3],
            'Ronda Casa Blanca' => ['Cecina de res' => 120, 'Cerdo' => 120, 'Pollo' => 100, 'Yuca' => 200, 'Papa' => 150, 'Cebolla' => 50, 'Aceite' => 60],

            // Porciones
            'Arroz blanco' => ['Arroz' => 180, 'Aceite' => 5, 'Ajo' => 2, 'Sal' => 2],
            'Arroz de trigo' => ['Trigo' => 150, 'Aceite' => 5, 'Ajo' => 2, 'Sal' => 2],
            'Yuca sancochada' => ['Yuca' => 300, 'Sal' => 2],
            'Papa sancochada' => ['Papa' => 300, 'Sal' => 2],
            'Camote' => ['Camote' => 300],
            'Yuca frita' => ['Yuca' => 300, 'Aceite' => 80, 'Sal' => 2],
            'Papa frita' => ['Papa' => 300, 'Aceite' => 80, 'Sal' => 2],
            'Choclo' => ['Choclo' => 300, 'Sal' => 2],
            'Guacamole' => ['Palta Fuerte' => 1, 'Cebolla' => 20, 'Tomate' => 20, 'Limón' => 1, 'Culantro' => 2, 'Sal' => 1],
            'Frejol' => ['Frejol' => 150, 'Cebolla' => 20, 'Aceite' => 10, 'Sal' => 2],
            'Huevo frito' => ['Huevo' => 1, 'Aceite' => 15],
            'Ensalada criolla' => ['Cebolla' => 120, 'Tomate' => 60, 'Limón' => 2, 'Culantro' => 3, 'Sal' => 2],
            'Ensalada fresca' => ['Lechuga' => 100, 'Tomate' => 80, 'Zanahoria' => 40, 'Cebolla' => 30, 'Limón' => 1, 'Aceite' => 10],

            // Cócteles
            'Pisco Sour' => ['Pisco' => 45, 'Limón' => 1, 'Jarabe de goma' => 30, 'Huevo' => [0.25, false], 'Hielo' => 100],
            'Piña Colada' => ['Ron' => 60, 'Crema de coco' => 60, 'Piña Golden' => 120, 'Hielo' => 120],
            'Cuba Libre' => ['Ron' => 60, 'Coca Cola para coctel' => 150, 'Limón' => 0.5, 'Hielo' => 100],
            'Daiquiri' => ['Ron' => 60, 'Limón' => 1, 'Jarabe de goma' => 15, 'Hielo' => 100],
            'Mojito de limón' => ['Ron' => 60, 'Hierba buena' => 5, 'Limón' => 1, 'Jarabe de goma' => 15, 'Agua con gas' => 100, 'Hielo' => 100],
            'Mojito de maracuyá' => ['Ron' => 60, 'Maracuyá' => 40, 'Hierba buena' => 5, 'Limón' => 1, 'Jarabe de goma' => 15, 'Agua con gas' => 100, 'Hielo' => 100],
            'Chilcano clásico' => ['Pisco' => 75, 'Ginger ale' => 150, 'Limón' => 0.5, 'Hielo' => 100],
            'Chilcano de piña' => ['Pisco' => 75, 'Piña Golden' => 50, 'Ginger ale' => 150, 'Limón' => 0.5, 'Hielo' => 100],
            'Chilcano de maracuyá' => ['Pisco' => 75, 'Maracuyá' => 40, 'Ginger ale' => 150, 'Limón' => 0.5, 'Hielo' => 100],
            'Sangría clásica' => $sangria,
            'Sangría red' => $sangria + ['Fresa' => 80],
            'Sangría hawaiana' => $sangria + ['Piña Golden' => 150],
            'Peruanísimo' => ['Pisco' => 60, 'Maracuyá' => 40, 'Jarabe de goma' => 15, 'Hielo' => 100],
            'Machu Picchu' => ['Pisco' => 45, 'Naranja' => 100, 'Granadina' => 15, 'Crema de menta' => 15, 'Hielo' => 100],
            'Canario' => ['Pisco' => 60, 'Naranja' => 130, 'Jarabe de goma' => 10, 'Hielo' => 100],
            'Destornillador' => ['Vodka' => 60, 'Naranja' => 200, 'Hielo' => 100],
            'Sex on the Beach' => ['Vodka' => 45, 'Licor de durazno' => 20, 'Naranja' => 100, 'Granadina' => 15, 'Hielo' => 100],
            'Laguna Azul' => ['Vodka' => 45, 'Curazao azul' => 30, 'Limón' => 1, 'Agua con gas' => 100, 'Hielo' => 100],

            // Frutas y postres
            'Piña Golden picada' => ['Piña Golden' => 300],
            'Mazamorra morada' => $mazamorra,
            'Arroz con leche' => $arrozConLeche,
            'Combinado' => ['Maíz morado' => 15, 'Piña Golden' => 15, 'Arroz' => 20, 'Leche evaporada' => 40, 'Azúcar' => 28, 'Harina' => 5, 'Canela' => 1],
            'Gelatina' => ['Gelatina en polvo' => 15, 'Piña Golden' => 30, 'Fresa' => 30, 'Azúcar' => 5],
            'Ensalada de frutas' => ['Piña Golden' => 80, 'Fresa' => 40, 'Manzana' => 60, 'Naranja' => 60],

            // Bebidas preparadas
            'Jarra de piña Golden' => $jarra('Piña Golden'),
            'Jarra de maracuyá' => $jarra('Maracuyá', 250),
            'Jarra de guanábana' => $jarra('Guanábana'),
            'Jarra de carambola' => $jarra('Carambola'),
            'Jarra de guayaba' => $jarra('Guayaba'),
            'Jarra de chicha morada' => ['Maíz morado' => 120, 'Piña Golden' => 100, 'Limón' => 3, 'Azúcar' => 80, 'Canela' => 2],
            'Limonada Frozen' => ['Limón' => [6, true], 'Azúcar' => 40, 'Hielo' => 300],
            'Piña Golden Frozen' => $frozen('Piña Golden', 250),
            'Maracuyá Frozen' => $frozen('Maracuyá', 150),

            // Cremoladas
            'Fresa con crema de coco' => $cremolada('Fresa', ['Crema de coco' => 60]),
            'Piña con crema de coco' => $cremolada('Piña Golden', ['Crema de coco' => 60]),
            'Maracuyá' => $cremolada('Maracuyá'),
            'Guanábana' => $cremolada('Guanábana'),
            'Carambola' => $cremolada('Carambola'),
            'Guayaba' => $cremolada('Guayaba'),
            'Coco' => ['Crema de coco' => 150, 'Azúcar' => 30, 'Hielo' => 200],
            'Dos, hasta tres sabores' => ['Fresa' => 75, 'Piña Golden' => 75, 'Azúcar' => 40, 'Hielo' => 200],
        ];
    }
}
