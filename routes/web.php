<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Mozo\DashboardController as MozoDashboard;
use App\Http\Controllers\Cajera\DashboardController as CajeraDashboard;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UsuarioController; // 👈 AGREGAR ESTA LÍNEA
use App\Http\Controllers\Admin\AlmacenController;
use App\Http\Controllers\Admin\SubcategoriaController;

Route::get('/', function () {
    return view('welcome');
});

// ============================================
// REDIRECCIÓN SEGÚN ROL
// ============================================
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if (!$user) {
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
});

// 2. MOZO - solo role_id = 2
Route::middleware(['auth', 'checkrole:mozo'])->prefix('mozo')->name('mozo.')->group(function () {
    Route::get('/dashboard', [MozoDashboard::class, 'index'])->name('dashboard');
});

// 3. CAJERA - solo role_id = 3
Route::middleware(['auth', 'checkrole:cajera'])->prefix('cajera')->name('cajera.')->group(function () {
    Route::get('/dashboard', [CajeraDashboard::class, 'index'])->name('dashboard');
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