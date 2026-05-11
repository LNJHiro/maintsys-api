<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaquinaController;
use App\Http\Controllers\TecnicoController;
use App\Http\Controllers\OrdemServicoController;
use App\Http\Controllers\HistoricoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;

// Página inicial redireciona para login
Route::get('/', function () {
    if (Auth::check()) {
    return redirect()->route('dashboard');
}
    return view('welcome');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Rotas protegidas por autenticação
Route::middleware('auth')->group(function () {

    // Máquinas
    Route::resource('maquinas', MaquinaController::class);

    // Técnicos
    Route::resource('tecnicos', TecnicoController::class);

    // Ordens de Serviço
    Route::resource('ordens', OrdemServicoController::class);

    // Histórico de manutenções
    Route::get('/historico', [HistoricoController::class, 'index'])->name('historico.index');
    Route::get('/historico/{id}', [HistoricoController::class, 'show'])->name('historico.show');
    Route::post('/historico', [HistoricoController::class, 'store'])->name('historico.store');
    Route::delete('/historico/{id}', [HistoricoController::class, 'destroy'])->name('historico.destroy');
    Route::get('/historico/maquina/{maquinaId}', [HistoricoController::class, 'porMaquina'])->name('historico.por-maquina');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';