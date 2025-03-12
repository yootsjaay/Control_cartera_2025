<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PolizasController;
use App\Http\Controllers\CompaniasController;
use App\Http\Controllers\SegurosRamoController;

// Redirección inicial
Route::redirect('/', '/login');

// Grupo de autenticación y verificación
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Perfil de usuario (acceso para todos los usuarios autenticados)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Ruta compartida para recursos (acceso para todos los usuarios autenticados)
    Route::get('/obtener-recursos', [PolizasController::class, 'obtenerRecursos']);

    // Gestión de Usuarios
    Route::prefix('usuarios')->middleware('permission:ver usuarios')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('usuarios.index');
        
        Route::middleware('permission:crear usuarios')->group(function () {
            Route::get('/crear', [UserController::class, 'create'])->name('usuarios.create');
            Route::post('/', [UserController::class, 'store'])->name('usuarios.store');
        });

        Route::middleware('permission:editar usuarios')->group(function () {
            Route::get('/{usuario}/editar', [UserController::class, 'edit'])->name('usuarios.edit');
            Route::patch('/{usuario}', [UserController::class, 'update'])->name('usuarios.update');
        });

        Route::middleware('permission:eliminar usuarios')->group(function () {
            Route::delete('/{usuario}', [UserController::class, 'destroy'])->name('usuarios.destroy');
        });
    });

    // Gestión de Pólizas
    Route::prefix('polizas')->group(function () {
        // Acceso básico
        Route::middleware('permission:ver pólizas')->group(function () {
            Route::get('/', [PolizasController::class, 'index'])->name('polizas.index');
            Route::get('/{poliza}', [PolizasController::class, 'show'])->name('polizas.show');
        });

        // Creación
        Route::middleware('permission:crear pólizas')->group(function () {
            Route::get('/create', [PolizasController::class, 'create'])->name('polizas.create');
            Route::post('/', [PolizasController::class, 'store'])->name('polizas.store');
        });

        // Edición
        Route::middleware('permission:editar pólizas')->group(function () {
            Route::get('/{poliza}/editar', [PolizasController::class, 'edit'])->name('polizas.edit');
            Route::patch('/{poliza}', [PolizasController::class, 'update'])->name('polizas.update');
        });

        // Eliminación
        Route::middleware('permission:eliminar pólizas')->group(function () {
            Route::delete('/{poliza}', [PolizasController::class, 'destroy'])->name('polizas.destroy');
        });

        // Funcionalidades especiales
        Route::middleware('permission:subir archivos de pólizas')->post('/subir-archivo', [PolizasController::class, 'subirArchivo'])->name('polizas.subir-archivo');
        Route::middleware('permission:renovacion de pólizas')->post('/{poliza}/renovar', [PolizasController::class, 'renovar'])->name('polizas.renovar');
    });

    // Módulo administrativo
    Route::middleware('permission:gestionar sistema')->group(function () {
        Route::resource('companias', CompaniasController::class)->except(['show']);
        Route::resource('seguros', SegurosRamoController::class)->except(['show']);
    });

    // Reportes
    Route::prefix('reportes')->middleware('permission:ver reportes')->group(function () {
        Route::get('/', [ReportesController::class, 'index'])->name('reportes.index');
        
        Route::middleware('permission:crear reportes')->group(function () {
            Route::get('/generar', [ReportesController::class, 'create'])->name('reportes.create');
            Route::post('/', [ReportesController::class, 'store'])->name('reportes.store');
        });

        Route::middleware('permission:exportar reportes')->get('/exportar', [ReportesController::class, 'exportar'])->name('reportes.exportar');
        Route::middleware('permission:imprimir reportes')->get('/imprimir', [ReportesController::class, 'imprimir'])->name('reportes.imprimir');
    });
});

// Autenticación
require __DIR__.'/auth.php';