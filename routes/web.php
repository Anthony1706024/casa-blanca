<?php

use App\Http\Controllers\Admin\AlmacenController;
use App\Http\Controllers\Admin\ContenidoController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\EgresoController;
use App\Http\Controllers\Admin\IngresoController;
use App\Http\Controllers\Admin\InsumoController;
use App\Http\Controllers\Admin\PrecioController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\VentaController;
use App\Http\Controllers\Cajera\CobroController;
use App\Http\Controllers\Cajera\ComandaController;
use App\Http\Controllers\Cajera\CuentaController;
use App\Http\Controllers\Cajera\DashboardController as CajeraDashboard;
use App\Http\Controllers\CartaController;
use App\Http\Controllers\ImpresionController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\Mozo\DashboardController as MozoDashboard;
use App\Http\Controllers\Mozo\PedidoController as MozoPedido;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReciboController;
use App\Http\Controllers\TicketeraAgenteController;
use App\Models\SitioItem;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::get('/', [InicioController::class, 'index']);

// ============================================
// CARTA PÚBLICA Y PREPEDIDO DEL CLIENTE
// ============================================
Route::get('/carta', [CartaController::class, 'index'])->name('carta.index');
Route::post('/carta/pedido', [CartaController::class, 'store'])->middleware('throttle:10,1')->name('carta.store');

// ============================================
// REDIRECCIÓN SEGÚN ROL
// ============================================
Route::get('/dashboard', function () {
    $user = auth()->user();

    if (! $user) {
        return redirect()->route('login');
    }

    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->isMozo()) {
        return redirect()->route('mozo.dashboard');
    } elseif ($user->isCajera()) {
        return redirect()->route('cajera.dashboard');
    }

    return redirect()->route('login');
})->middleware(['auth'])->name('dashboard');

// ============================================
// RUTAS POR ROL
// ============================================

Route::middleware(['auth', 'checkrole:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{id}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

    // Almacén
    Route::get('/almacen', [AlmacenController::class, 'index'])->name('almacen.index');
    Route::put('/almacen/{id}/stock', [AlmacenController::class, 'updateStock'])->name('almacen.stock');
    Route::get('/almacen/crear', [AlmacenController::class, 'create'])->name('almacen.create');
    Route::post('/almacen', [AlmacenController::class, 'store'])->name('almacen.store');
    Route::get('/almacen/{id}/editar', [AlmacenController::class, 'edit'])->name('almacen.edit');
    Route::put('/almacen/{id}', [AlmacenController::class, 'update'])->name('almacen.update');
    Route::delete('/almacen/{id}', [AlmacenController::class, 'destroy'])->name('almacen.destroy');

    // Categorías
    Route::get('/categorias', [AlmacenController::class, 'categoriasIndex'])->name('categorias.index');
    Route::post('/categorias', [AlmacenController::class, 'categoriasStore'])->name('categorias.store');
    Route::delete('/categorias/{id}', [AlmacenController::class, 'categoriasDestroy'])->name('categorias.destroy');

    // ====== SUBCATEGORÍAS ======
    Route::get('/subcategorias', [AlmacenController::class, 'subcategoriasIndex'])->name('subcategorias.index');
    Route::post('/subcategorias', [AlmacenController::class, 'subcategoriasStore'])->name('subcategorias.store');
    Route::delete('/subcategorias/{id}', [AlmacenController::class, 'subcategoriasDestroy'])->name('subcategorias.destroy');
    Route::get('/subcategorias/por-categoria/{categoria_id}', [AlmacenController::class, 'getSubcategorias'])->name('subcategorias.por-categoria');

    // Insumos
    Route::get('/insumos', [InsumoController::class, 'index'])->name('insumos.index');
    Route::get('/insumos/crear', [InsumoController::class, 'create'])->name('insumos.create');
    Route::post('/insumos', [InsumoController::class, 'store'])->name('insumos.store');
    Route::get('/insumos/categorias', [InsumoController::class, 'categoriasIndex'])->name('insumos.categorias.index');
    Route::post('/insumos/categorias', [InsumoController::class, 'categoriasStore'])->name('insumos.categorias.store');
    Route::delete('/insumos/categorias/{id}', [InsumoController::class, 'categoriasDestroy'])->name('insumos.categorias.destroy');
    Route::get('/insumos/{id}/editar', [InsumoController::class, 'edit'])->name('insumos.edit');
    Route::put('/insumos/{id}', [InsumoController::class, 'update'])->name('insumos.update');
    Route::post('/insumos/{id}/ingreso', [InsumoController::class, 'ingreso'])->name('insumos.ingreso');
    Route::delete('/insumos/{id}', [InsumoController::class, 'destroy'])->name('insumos.destroy');

    // Ventas
    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');

    // Finanzas: ingresos diarios y egresos (día, semana, mes, año o rango)
    Route::get('/ingresos', [IngresoController::class, 'index'])->name('ingresos.index');
    Route::get('/ingresos/exportar', [IngresoController::class, 'exportar'])->name('ingresos.exportar');
    Route::get('/egresos', [EgresoController::class, 'index'])->name('egresos.index');
    Route::post('/egresos', [EgresoController::class, 'store'])->name('egresos.store');
    Route::get('/egresos/exportar', [EgresoController::class, 'exportar'])->name('egresos.exportar');
    Route::post('/egresos/categorias', [EgresoController::class, 'categoriasStore'])->name('egresos.categorias.store');
    Route::delete('/egresos/categorias/{id}', [EgresoController::class, 'categoriasDestroy'])->name('egresos.categorias.destroy');
    Route::get('/egresos/{id}/editar', [EgresoController::class, 'edit'])->name('egresos.edit');
    Route::put('/egresos/{id}', [EgresoController::class, 'update'])->name('egresos.update');
    Route::delete('/egresos/{id}', [EgresoController::class, 'destroy'])->name('egresos.destroy');

    // Precios
    Route::get('/precios', [PrecioController::class, 'index'])->name('precios.index');
    Route::put('/precios', [PrecioController::class, 'actualizar'])->name('precios.actualizar');
    Route::get('/precios/historial', [PrecioController::class, 'historial'])->name('precios.historial');
    Route::get('/precios/ventas', [PrecioController::class, 'ventas'])->name('precios.ventas');

    // Contenido de la página web
    Route::get('/contenido', [ContenidoController::class, 'index'])->name('contenido.index');
    Route::put('/contenido', [ContenidoController::class, 'guardar'])->name('contenido.guardar');
    Route::get('/contenido/secciones/{tipo}', [ContenidoController::class, 'items'])->whereIn('tipo', array_keys(SitioItem::TIPOS))->name('contenido.items');
    Route::post('/contenido/secciones/{tipo}', [ContenidoController::class, 'itemStore'])->whereIn('tipo', array_keys(SitioItem::TIPOS))->name('contenido.items.store');
    Route::get('/contenido/elementos/{id}/editar', [ContenidoController::class, 'itemEdit'])->name('contenido.items.edit');
    Route::put('/contenido/elementos/{id}', [ContenidoController::class, 'itemUpdate'])->name('contenido.items.update');
    Route::delete('/contenido/elementos/{id}', [ContenidoController::class, 'itemDestroy'])->name('contenido.items.destroy');
});

// 2. MOZO - solo role_id = 2
Route::middleware(['auth', 'checkrole:mozo'])->prefix('mozo')->name('mozo.')->group(function () {
    Route::get('/dashboard', [MozoDashboard::class, 'index'])->name('dashboard');

    // Pedidos
    Route::get('/pedidos', [MozoPedido::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/crear', [MozoPedido::class, 'create'])->name('pedidos.create');
    Route::post('/pedidos', [MozoPedido::class, 'store'])->name('pedidos.store');
    Route::post('/pedidos/{id}/anular', [MozoPedido::class, 'anular'])->name('pedidos.anular');
    // Sus pedidos: ver, agregar adicionales y quitar platos ("crear" va antes de {id})
    Route::get('/pedidos/{id}', [MozoPedido::class, 'show'])->whereNumber('id')->name('pedidos.show');
    Route::get('/pedidos/{id}/adicionales', [MozoPedido::class, 'adicionales'])->name('pedidos.adicionales');
    Route::post('/pedidos/{id}/adicionales', [MozoPedido::class, 'adicionalesStore'])->name('pedidos.adicionales.store');
    Route::post('/pedidos/{id}/items/{item}/quitar', [MozoPedido::class, 'quitarItem'])->name('pedidos.items.quitar');
});

// 3. CAJERA - solo role_id = 3
Route::middleware(['auth', 'checkrole:cajera'])->prefix('cajera')->name('cajera.')->group(function () {
    Route::get('/dashboard', [CajeraDashboard::class, 'index'])->name('dashboard');

    // Cobros. "crear" va antes de {id} para que no se tome como un id.
    Route::get('/pedidos', [CobroController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/crear', [CobroController::class, 'create'])->name('pedidos.create');
    Route::post('/pedidos', [CobroController::class, 'store'])->name('pedidos.store');
    Route::get('/pedidos/{id}', [CobroController::class, 'show'])->name('pedidos.show');
    Route::post('/pedidos/{id}/cobrar', [CobroController::class, 'cobrar'])->name('pedidos.cobrar');
    Route::post('/pedidos/{id}/anular', [CobroController::class, 'anular'])->name('pedidos.anular');
    Route::get('/recibos', [CobroController::class, 'recibos'])->name('recibos.index');

    // Adicionales para la misma mesa y tickets de cocina
    Route::get('/pedidos/{id}/adicionales', [CobroController::class, 'adicionales'])->name('pedidos.adicionales');
    Route::post('/pedidos/{id}/adicionales', [CobroController::class, 'adicionalesStore'])->name('pedidos.adicionales.store');
    Route::post('/pedidos/{id}/items/{item}/quitar', [CobroController::class, 'quitarItem'])->name('pedidos.items.quitar');
    Route::post('/pedidos/{id}/comanda', [ComandaController::class, 'imprimir'])->name('pedidos.comanda');
    // Cuenta para la mesa, antes de cobrar (no cambia el estado)
    Route::get('/pedidos/{id}/cuenta', [CuentaController::class, 'ver'])->name('pedidos.cuenta');
    Route::post('/pedidos/{id}/cuenta', [CuentaController::class, 'imprimir'])->name('pedidos.cuenta.imprimir');
    Route::get('/pedidos/{id}/comanda', [ComandaController::class, 'ver'])->name('pedidos.comanda.ver');
});

// Recibo imprimible: lo emite caja y lo consulta el administrador
Route::middleware(['auth', 'checkrole:admin,cajera'])->group(function () {
    Route::get('/recibos/{id}', [ReciboController::class, 'show'])->name('recibos.show');
    Route::post('/recibos/{id}/imprimir', [ReciboController::class, 'imprimir'])->name('recibos.imprimir');
    // Borra el recibo, sus pagos y su pedido (no devuelve stock)
    Route::delete('/recibos/{id}', [ReciboController::class, 'destroy'])->name('recibos.destroy');

    // Seguimiento de la cola de impresión
    Route::get('/impresiones', [ImpresionController::class, 'index'])->name('impresiones.index');
    Route::post('/impresiones/{id}/reintentar', [ImpresionController::class, 'reintentar'])->name('impresiones.reintentar');
});

// ============================================
// AGENTE DE LA TICKETERA (PC de caja): sin sesión ni CSRF, con clave Bearer
// ============================================
Route::prefix('ticketera')->name('ticketera.')
    ->middleware(['ticketera.clave', 'throttle:120,1'])
    ->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, ValidateCsrfToken::class])
    ->group(function () {
        Route::get('/pendientes', [TicketeraAgenteController::class, 'pendientes'])->name('pendientes');
        Route::post('/{id}/impresa', [TicketeraAgenteController::class, 'impresa'])->whereNumber('id')->name('impresa');
        Route::post('/{id}/error', [TicketeraAgenteController::class, 'error'])->whereNumber('id')->name('error');
    });

// ============================================
// RUTAS DE PERFIL (TODOS LOS ROLES)
// ============================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
