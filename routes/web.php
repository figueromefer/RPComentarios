<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\RecetaUsuarioController;
use Illuminate\Support\Facades\Route;

/*Route::get('/', function () {
    return view('welcome');
});*/

Route::get('/', function () {
    return redirect()->route('comentarios.index');
});

Route::middleware('auth')->group(function () {
    Route::resource('comentarios', ComentarioController::class)->only(['index','edit','update','show']);

    Route::get('recetas-usuario', [RecetaUsuarioController::class, 'index'])->name('recetas-usuario.index');
    Route::get('recetas-usuario/{receta}/edit', [RecetaUsuarioController::class, 'edit'])->name('recetas-usuario.edit');
    Route::put('recetas-usuario/{receta}', [RecetaUsuarioController::class, 'update'])->name('recetas-usuario.update');
    Route::patch('recetas-usuario/{receta}/status', [RecetaUsuarioController::class, 'changeStatus'])->name('recetas-usuario.status');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
