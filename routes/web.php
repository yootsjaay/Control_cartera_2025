<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PolizasController;
use App\Http\Controllers\CompaniasController;
use App\Http\Controllers\SegurosRamoController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DashboardController;


Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/polizas/recursos', [PolizasController::class, 'obtenerRecursos']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('polizas', PolizasController::class);
    // Descargar archivo PDF de póliza

    // Usuarios (ajustado para coincidir con la carpeta 'user')
    Route::prefix('usuarios')->middleware('permission:ver usuarios')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('user.index');
        Route::middleware('permission:crear usuarios')->group(function () {
            Route::get('/create', [UserController::class, 'create'])->name('user.create');
            Route::post('/', [UserController::class, 'store'])->name('user.store');
        });
        Route::middleware('permission:editar usuarios')->group(function () {
            Route::get('/{usuario}/editar', [UserController::class, 'edit'])->name('user.edit');
            Route::patch('/{usuario}', [UserController::class, 'update'])->name('user.update');
        });
        Route::middleware('permission:eliminar usuarios')->group(function () {
            Route::delete('/{usuario}', [UserController::class, 'destroy'])->name('user.destroy');
        });
    });

    // Pólizas (corregido el prefijo para 'create')
    Route::prefix('polizas')->group(function () {
        Route::middleware('permission:ver pólizas')->group(function () {
            
            Route::get('/', [PolizasController::class, 'index'])->name('polizas.index');
            Route::middleware('permission:crear pólizas')->group(function () {
                Route::get('/create', [PolizasController::class, 'create'])->name('polizas.create'); // Corregido de 'polizas/create' a '/create'
                Route::post('/', [PolizasController::class, 'store'])->name('polizas.store');
            }); 
            Route::get('/{poliza}', [PolizasController::class, 'show'])->name('polizas.show');
            Route::get('/renovaciones', [PolizasController::class, 'renovaciones'])->name('polizas.renovaciones');
            Route::get('/vencidas', [PolizasController::class, 'vencidas'])->name('polizas.vencidas')->middleware('permission:pólizas vencidas');
            Route::get('/pendientes', [PolizasController::class, 'pendientes'])->name('polizas.pendientes')->middleware('permission:pólizas pendientes');
        });
       
        Route::middleware('permission:editar pólizas')->group(function () {
            Route::get('/{poliza}/editar', [PolizasController::class, 'edit'])->name('polizas.edit');
            Route::patch('/{poliza}', [PolizasController::class, 'update'])->name('polizas.update');
        });
        Route::middleware('permission:eliminar pólizas')->group(function () {
            Route::delete('/{poliza}', [PolizasController::class, 'destroy'])->name('polizas.destroy');
        });
        Route::middleware('permission:subir archivos de pólizas')->group(function () {
            Route::get('/archivos', [PolizasController::class, 'archivos'])->name('polizas.archivos');
            Route::post('/subir-archivo', [PolizasController::class, 'subirArchivo'])->name('polizas.subir-archivo');
        });
        Route::middleware('permission:renovacion de pólizas')->post('/{poliza}/renovar', [PolizasController::class, 'renovar'])->name('polizas.renovar');
    });

    // Clientes
    Route::prefix('clientes')->middleware('permission:ver clientes')->group(function () {
        Route::get('/', [ClientesController::class, 'index'])->name('clientes.index');
        Route::middleware('permission:crear clientes')->group(function () {
            Route::get('/create', [ClientesController::class, 'create'])->name('clientes.create');
            Route::post('/', [ClientesController::class, 'store'])->name('clientes.store');
        });
    });

    // Módulo administrativo
    Route::middleware('permission:gestionar sistema')->group(function () {
        Route::resource('companias', CompaniasController::class)->except(['show']);
        Route::resource('seguros', SegurosRamoController::class)->except(['show']);
    });

    // Reportes
    Route::prefix('reportes')->middleware('permission:ver reportes')->group(function () {
        Route::get('/', [ReportesController::class, 'index'])->name('reportes.index');
        Route::get('/exportar', [ReportesController::class, 'exportar'])->name('reportes.exportar')->middleware('permission:exportar reportes');
        Route::get('/estadisticas', [ReportesController::class, 'estadisticas'])->name('reportes.estadisticas');
    });

    // Roles
    Route::prefix('roles')->middleware('permission:ver roles y permisos')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/crear', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/', [RoleController::class, 'store'])->name('roles.store');
        Route::middleware('permission:editar usuarios')->group(function () {
            Route::get('/{roles}/editar', [UserController::class, 'edit'])->name('roles.edit');
            Route::patch('/{roles}', [UserController::class, 'update'])->name('roles.update');
        });
        Route::middleware('permission:eliminar usuarios')->group(function () {
            Route::delete('/{roles}', [UserController::class, 'destroy'])->name('roles.destroy');
        });
    });
    
    
});

require __DIR__.'/auth.php';