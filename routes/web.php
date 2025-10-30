<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubtareaController;
use App\Http\Controllers\TareaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Ruta raíz: redirige según autenticación
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('tareas.proximos-siete-dias')
        : redirect()->route('login');
})->name('home');

Route::get('/dashboard', function () {
    return redirect()->route('tareas.proximos-siete-dias');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de Tareas
    Route::get('/tareas', [TareaController::class, 'index'])->name('tareas.index');
    Route::get('/tareas/proximos-siete-dias', [TareaController::class, 'proximosSieteDias'])->name('tareas.proximos-siete-dias');
    Route::get('/tareas/todas', [TareaController::class, 'todasMisTareas'])->name('tareas.todas');
    Route::get('/tareas/calendario', [TareaController::class, 'miCalendario'])->name('tareas.calendario');
    Route::post('/tareas', [TareaController::class, 'store'])->name('tareas.store');
    Route::match(['put', 'patch'], '/tareas/{tarea}', [TareaController::class, 'update'])->name('tareas.update');
    Route::delete('/tareas/{tarea}', [TareaController::class, 'destroy'])->name('tareas.destroy');
    Route::patch('/tareas/{tarea}/toggle', [TareaController::class, 'toggle'])->name('tareas.toggle');

    // Rutas de Subtareas (anidadas en tareas)
    Route::prefix('tareas/{tarea}')->group(function () {
        Route::post('subtareas', [SubtareaController::class, 'store'])->name('subtareas.store');
        Route::patch('subtareas/{subtarea}', [SubtareaController::class, 'update'])->name('subtareas.update');
        Route::delete('subtareas/{subtarea}', [SubtareaController::class, 'destroy'])->name('subtareas.destroy');
        Route::post('subtareas/{subtarea}/toggle', [SubtareaController::class, 'toggle'])->name('subtareas.toggle');
    });

    // Rutas de Categorías
    Route::resource('categorias', CategoriaController::class)->except(['show', 'create', 'edit']);
});

require __DIR__ . '/auth.php';
