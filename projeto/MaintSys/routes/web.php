<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaquinaController;
use App\Http\Controllers\TecnicoController;
use App\Http\Controllers\OrdemServicoController;
use App\Http\Controllers\HistoricoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\UserManagementController;
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
    Route::middleware('perm:maquinas.criar')->group(function () {
        Route::get('/maquinas/create', [MaquinaController::class, 'create'])->name('maquinas.create');
        Route::post('/maquinas', [MaquinaController::class, 'store'])->name('maquinas.store');
    });
    Route::get('/maquinas', [MaquinaController::class, 'index'])->name('maquinas.index');
    Route::middleware('perm:maquinas.editar')->group(function () {
        Route::get('/maquinas/{id}/edit', [MaquinaController::class, 'edit'])->name('maquinas.edit');
        Route::put('/maquinas/{id}', [MaquinaController::class, 'update'])->name('maquinas.update');
    });
    Route::middleware('perm:maquinas.deletar')->delete('/maquinas/{id}', [MaquinaController::class, 'destroy'])->name('maquinas.destroy');
    Route::get('/maquinas/{id}', [MaquinaController::class, 'show'])->name('maquinas.show');

    // Técnicos
    Route::middleware('perm:tecnicos.criar')->group(function () {
        Route::get('/tecnicos/create', [TecnicoController::class, 'create'])->name('tecnicos.create');
        Route::post('/tecnicos', [TecnicoController::class, 'store'])->name('tecnicos.store');
    });
    Route::middleware('perm:tecnicos.visualizar')->group(function () {
        Route::get('/tecnicos', [TecnicoController::class, 'index'])->name('tecnicos.index');
    });
    Route::middleware('perm:tecnicos.editar')->group(function () {
        Route::get('/tecnicos/{id}/edit', [TecnicoController::class, 'edit'])->name('tecnicos.edit');
        Route::put('/tecnicos/{id}', [TecnicoController::class, 'update'])->name('tecnicos.update');
    });
    Route::middleware('perm:tecnicos.deletar')->delete('/tecnicos/{id}', [TecnicoController::class, 'destroy'])->name('tecnicos.destroy');
    Route::middleware('perm:tecnicos.visualizar')->get('/tecnicos/{id}', [TecnicoController::class, 'show'])->name('tecnicos.show');

    // Ordens de Serviço
    Route::middleware('perm:ordens.criar')->group(function () {
        Route::get('/ordens/create', [OrdemServicoController::class, 'create'])->name('ordens.create');
        Route::post('/ordens', [OrdemServicoController::class, 'store'])->name('ordens.store');
    });
    Route::middleware('perm:ordens.visualizar')->group(function () {
        Route::get('/ordens', [OrdemServicoController::class, 'index'])->name('ordens.index');
    });
    Route::middleware('perm:ordens.editar')->group(function () {
        Route::get('/ordens/{id}/edit', [OrdemServicoController::class, 'edit'])->name('ordens.edit');
        Route::put('/ordens/{id}', [OrdemServicoController::class, 'update'])->name('ordens.update');
    });
    Route::middleware('perm:ordens.deletar')->delete('/ordens/{id}', [OrdemServicoController::class, 'destroy'])->name('ordens.destroy');
    Route::middleware('perm:ordens.visualizar')->get('/ordens/{id}', [OrdemServicoController::class, 'show'])->name('ordens.show');

    // Histórico de manutenções
    Route::middleware('perm:historico.visualizar')->group(function () {
        Route::get('/historico', [HistoricoController::class, 'index'])->name('historico.index');
        Route::get('/historico/maquina/{maquinaId}', [HistoricoController::class, 'porMaquina'])->name('historico.por-maquina');
    });
    Route::middleware('perm:historico.criar')->post('/historico', [HistoricoController::class, 'store'])->name('historico.store');
    Route::middleware('perm:historico.deletar')->delete('/historico/{id}', [HistoricoController::class, 'destroy'])->name('historico.destroy');
    Route::middleware('perm:historico.visualizar')->get('/historico/{id}', [HistoricoController::class, 'show'])->name('historico.show');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Gerenciamento de Acesso — admin_master + admin
    Route::middleware('admin_access')->group(function () {
        Route::get('/acesso', [AccessController::class, 'index'])->name('acesso.index');
        Route::post('/acesso/usuario/{user}/permissoes', [AccessController::class, 'updateUserPermissions'])->name('acesso.updateUserPermissions');
        Route::post('/acesso/role/{role}', [AccessController::class, 'updateRole'])->name('acesso.updateRole');
        Route::get('/acesso/usuarios', [AccessController::class, 'usuarios'])->name('acesso.usuarios');
        Route::patch('/acesso/usuario/{user}', [AccessController::class, 'updateUsuario'])->name('acesso.updateUsuario');
    });

    // Gerenciamento de usuários — admin_master + admin (mas admin não pode editar admin_master)
    Route::middleware('admin_access')->group(function () {
        Route::get('/usuarios', [UserManagementController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/criar', [UserManagementController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UserManagementController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{user}/editar', [UserManagementController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{user}', [UserManagementController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/{user}', [UserManagementController::class, 'destroy'])->name('usuarios.destroy');
        Route::get('/usuarios/{user}/permissoes', [UserManagementController::class, 'showPermissions'])->name('usuarios.permissions');
    });
});

require __DIR__.'/auth.php';