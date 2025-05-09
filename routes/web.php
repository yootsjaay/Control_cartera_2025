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
use App\Http\Controllers\GroupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PolicyDashboardController;
use App\Http\Controllers\AuthTokenController;

Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function () {

    // 🏠 Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 👤 Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 🔒 Validación desde Python
    Route::post('/validar-token', [AuthTokenController::class, 'validar']);

    // 🔔 Notificaciones
    Route::get('/notificaciones/index', [PolicyDashboardController::class, 'index'])->name('policies.notificaciones');

    // 👥 Usuarios
    Route::prefix('usuarios')->middleware('permission:ver usuarios')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('user.index');
        Route::resource('users', UserController::class)->names('users');

    });

    // 📄 Pólizas
    Route::prefix('polizas')->group(function () {
        Route::middleware('permission:ver polizas')->group(function () {
            Route::get('/', [PolizasController::class, 'index'])->name('polizas.index');
            Route::get('/{poliza}', [PolizasController::class, 'show'])->name('polizas.show');
            Route::get('/renovaciones', [PolizasController::class, 'renovaciones'])->name('polizas.renovaciones');
            Route::get('/vencidas', [PolizasController::class, 'vencidas'])->name('polizas.vencidas');
            Route::get('/pendientes', [PolizasController::class, 'pendientes'])->name('polizas.pendientes');
        });

        Route::middleware('permission:crear polizas')->group(function () {
            Route::get('/create', [PolizasController::class, 'create'])->name('polizas.create');
            Route::post('/', [PolizasController::class, 'store'])->name('polizas.store');
        });

        Route::middleware('permission:editar polizas')->group(function () {
            Route::get('/{poliza}/editar', [PolizasController::class, 'edit'])->name('polizas.edit');
            Route::patch('/{poliza}', [PolizasController::class, 'update'])->name('polizas.update');
            Route::get('/obtener-datos-seguro/{seguroId}', [PolizasController::class, 'obtenerDatosSeguro']);
            Route::post('/{poliza}/notificar', [PolizasController::class, 'notificar'])->name('polizas.notificar');
        });

        Route::middleware('permission:eliminar polizas')->delete('/{poliza}', [PolizasController::class, 'destroy'])->name('polizas.destroy');

        Route::middleware('permission:subir archivos de pólizas')->group(function () {
            Route::get('/archivos', [PolizasController::class, 'archivos'])->name('polizas.archivos');
            Route::post('/subir-archivo', [PolizasController::class, 'subirArchivo'])->name('polizas.subir-archivo');
        });

        Route::middleware('permission:renovacion de pólizas')->post('/{poliza}/renovar', [PolizasController::class, 'renovar'])->name('polizas.renovar');

        Route::post('/recursos', [PolizasController::class, 'obtenerRecursos']);
    });

   

    // 🛠️ Módulo administrativo
    Route::middleware('permission:gestionar sistema')->group(function () {
        Route::resource('companias', CompaniasController::class)->except(['show']);
        Route::resource('seguros', SegurosRamoController::class)->except(['show']);
    });

   

    // 🧩 Roles y permisos
    Route::prefix('roles')->middleware('permission:ver roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/crear', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/', [RoleController::class, 'store'])->name('roles.store');

        Route::middleware('permission:editar roles')->group(function () {
            Route::get('/{role}/editar', [RoleController::class, 'edit'])->name('roles.edit');
            Route::match(['put', 'patch'], '/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        });

        Route::middleware('permission:eliminar roles')->delete('/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

   
});

require __DIR__.'/auth.php';
