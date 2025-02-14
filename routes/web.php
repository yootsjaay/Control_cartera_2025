<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PolizasController;
use App\Http\Controllers\CompaniasController;
use App\Http\Controllers\SegurosRamoController;

// Ruta principal que redirige al login
Route::get('/', function () {
    return redirect()->route('login');
});

// Ruta del dashboard, accesible solo para usuarios autenticados
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Agrupación de rutas protegidas por autenticación
Route::middleware(['auth', 'verified'])->group(function () {

    // Rutas de perfil de usuario (para cualquier usuario autenticado)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas específicas para el rol `admin`
    Route::middleware('role:admin')->group(function () {
        // Rutas de gestión de usuarios (solo admin puede crear, editar y eliminar)
        Route::resource('/user', UserController::class)->except(['index']);
        // Rutas de roles (solo admin puede gestionar roles)
        Route::resource('/roles', RoleController::class);
        // Rutas de pólizas (solo admin puede gestionar pólizas)
        Route::resource('/polizas', PolizasController::class);
        // web.php
     
        // Ruta para obtener los seguros relacionados con una compañía
        Route::get('/obtener-recursos', [PolizasController::class, 'obtenerRecursos']);
        Route::resource('/companias', CompaniasController::class);
        Route::resource('/seguros', SegurosRamoController::class);


    });

    // Rutas específicas para el rol `user`
    Route::middleware('role:user')->group(function () {
        // Rutas de pólizas (solo user puede ver pólizas)
        Route::resource('/polizas', PolizasController::class)->only(['index', 'show']);
    });

    // Rutas accesibles por `admin` y `user` (ver usuarios)
    Route::middleware('permission:ver usuarios')->group(function () {
        Route::get('/user', [UserController::class, 'index'])->name('user.index');
    });

    // Rutas adicionales con permisos específicos
    Route::middleware('permission:crear usuarios')->group(function () {
        Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
        Route::post('/user', [UserController::class, 'store'])->name('user.store');
    });

    Route::middleware('permission:editar usuarios')->group(function () {
        Route::get('/user/{user}/edit', [UserController::class, 'edit'])->name('user.edit');
        Route::patch('/user/{user}', [UserController::class, 'update'])->name('user.update');
    });

    Route::middleware('permission:eliminar usuarios')->group(function () {
        Route::delete('/user/{user}', [UserController::class, 'destroy'])->name('user.destroy');
    });

    // Rutas de pólizas
    Route::middleware('permission:ver pólizas')->group(function () {
        Route::get('/polizas', [PolizasController::class, 'index'])->name('polizas.index');
        Route::get('/polizas/{poliza}', [PolizasController::class, 'show'])->name('polizas.show');
    });
    Route::middleware('permission:crear pólizas')->group(function () {
        Route::get('/polizas/create', [PolizasController::class, 'create'])->name('polizas.create');
        Route::post('/polizas', [PolizasController::class,'store'])->name('polizas.store');
    });
    Route::middleware('permission:editar pólizas')->group(function () {
        Route::get('/polizas/{poliza}/edit', [PolizasController::class, 'edit'])->name('polizas.edit');
        Route::patch('/polizas/{poliza}', [PolizasController::class, 'update'])->name('polizas.update');
    });
    Route::middleware('permission:eliminar pólizas')->group(function () {
        Route::delete('/polizas/{poliza}', [PolizasController::class, 'destroy'])->name('polizas.destroy');
    });
});

// Rutas de autenticación
require __DIR__.'/auth.php';
