<?php

/**
 * ROTAS: web.php — MaintSys
 *
 * Todas as rotas do sistema estão aqui.
 * Organização:
 * - Rotas públicas (sem autenticação)
 * - Dashboard (apenas autenticado)
 * - CRUD de cada módulo (com middleware de permissão)
 * - Autenticação (em auth.php)
 */

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

// GET  /         → Redireciona para dashboard se autenticado, ou welcome
// Sem autenticação obrigatória
Route::get('/', function () {
    if (Auth::check()) {
    return redirect()->route('dashboard');
}
    return view('welcome');
});

// ========================================
// DASHBOARD
// ========================================

// GET  /dashboard          → DashboardController@index
// Mostrar painel com stats, alertas, O.S. recentes, manutenções
// Middleware: auth + verified
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Rotas protegidas por autenticação
Route::middleware('auth')->group(function () {

    // ========================================
    // MÁQUINAS (CRUD)
    // ========================================

    // GET  /maquinas/create    → Formulário de criar máquina (permissão: maquinas.criar)
    // POST /maquinas           → Salvar nova máquina (permissão: maquinas.criar)
    Route::middleware('perm:maquinas.criar')->group(function () {
        Route::get('/maquinas/create', [MaquinaController::class, 'create'])->name('maquinas.create');
        Route::post('/maquinas', [MaquinaController::class, 'store'])->name('maquinas.store');
    });

    // GET  /maquinas           → Listar todas as máquinas (permissão: maquinas.visualizar)
    // GET  /maquinas/{id}      → Ver detalhes da máquina (permissão: maquinas.visualizar)
    Route::middleware('perm:maquinas.visualizar')->group(function () {
        Route::get('/maquinas', [MaquinaController::class, 'index'])->name('maquinas.index');
        Route::get('/maquinas/{id}', [MaquinaController::class, 'show'])->name('maquinas.show');
    });

    // GET  /maquinas/{id}/edit → Formulário de editar máquina (permissão: maquinas.editar)
    // PUT  /maquinas/{id}      → Salvar alterações da máquina (permissão: maquinas.editar)
    Route::middleware('perm:maquinas.editar')->group(function () {
        Route::get('/maquinas/{id}/edit', [MaquinaController::class, 'edit'])->name('maquinas.edit');
        Route::put('/maquinas/{id}', [MaquinaController::class, 'update'])->name('maquinas.update');
    });

    // DELETE /maquinas/{id}    → Deletar máquina (permissão: maquinas.deletar)
    Route::middleware('perm:maquinas.deletar')->delete('/maquinas/{id}', [MaquinaController::class, 'destroy'])->name('maquinas.destroy');

    // ========================================
    // TÉCNICOS (CRUD)
    // ========================================

    // GET  /tecnicos/create    → Formulário de criar técnico (permissão: tecnicos.criar)
    // POST /tecnicos           → Salvar novo técnico + criar User associado (permissão: tecnicos.criar)
    Route::middleware('perm:tecnicos.criar')->group(function () {
        Route::get('/tecnicos/create', [TecnicoController::class, 'create'])->name('tecnicos.create');
        Route::post('/tecnicos', [TecnicoController::class, 'store'])->name('tecnicos.store');
    });

    // GET  /tecnicos           → Listar todos os técnicos (permissão: tecnicos.visualizar)
    Route::middleware('perm:tecnicos.visualizar')->group(function () {
        Route::get('/tecnicos', [TecnicoController::class, 'index'])->name('tecnicos.index');
    });

    // GET  /tecnicos/{id}/edit → Formulário de editar técnico (permissão: tecnicos.editar)
    // PUT  /tecnicos/{id}      → Salvar alterações do técnico + User (permissão: tecnicos.editar)
    Route::middleware('perm:tecnicos.editar')->group(function () {
        Route::get('/tecnicos/{id}/edit', [TecnicoController::class, 'edit'])->name('tecnicos.edit');
        Route::put('/tecnicos/{id}', [TecnicoController::class, 'update'])->name('tecnicos.update');
    });

    // DELETE /tecnicos/{id}    → Deletar técnico (permissão: tecnicos.deletar)
    Route::middleware('perm:tecnicos.deletar')->delete('/tecnicos/{id}', [TecnicoController::class, 'destroy'])->name('tecnicos.destroy');

    // GET  /tecnicos/{id}      → Ver detalhes do técnico + O.S. (permissão: tecnicos.visualizar)
    Route::middleware('perm:tecnicos.visualizar')->get('/tecnicos/{id}', [TecnicoController::class, 'show'])->name('tecnicos.show');

    // ========================================
    // ORDENS DE SERVIÇO (CRUD + AUTOMAÇÕES)
    // ========================================

    // GET  /ordens/create      → Formulário de criar O.S. (permissão: ordens.criar)
    // POST /ordens             → Salvar nova O.S. + gera número + sincroniza máquina + notifica técnico (permissão: ordens.criar)
    Route::middleware('perm:ordens.criar')->group(function () {
        Route::get('/ordens/create', [OrdemServicoController::class, 'create'])->name('ordens.create');
        Route::post('/ordens', [OrdemServicoController::class, 'store'])->name('ordens.store');
    });

    // GET  /ordens             → Listar O.S. com filtros (permissão: ordens.visualizar)
    // GET  /ordens/exportar    → CSV de todas as O.S. com filtros (permissão: ordens.visualizar)
    // GET  /ordens/{id}/exportar → CSV individual da O.S. (permissão: ordens.visualizar)
    Route::middleware('perm:ordens.visualizar')->group(function () {
        Route::get('/ordens', [OrdemServicoController::class, 'index'])->name('ordens.index');
        Route::get('/ordens/exportar', [OrdemServicoController::class, 'exportar'])->name('ordens.exportar');
        Route::get('/ordens/{id}/exportar', [OrdemServicoController::class, 'exportarSingle'])->name('ordens.exportar-single');
    });

    // GET  /ordens/{id}/edit   → Formulário de editar/concluir O.S. (permissão: ordens.editar)
    // PUT  /ordens/{id}        → Salvar edição/conclusão + cria histórico + próxima preventiva + sincroniza máquina (permissão: ordens.editar)
    //                           ⚠ CRÍTICO: Se mudou para 'concluida', cria histórico e próxima preventiva automaticamente
    Route::middleware('perm:ordens.editar')->group(function () {
        Route::get('/ordens/{id}/edit', [OrdemServicoController::class, 'edit'])->name('ordens.edit');
        Route::put('/ordens/{id}', [OrdemServicoController::class, 'update'])->name('ordens.update');
    });

    // DELETE /ordens/{id}      → Deletar O.S. + sincroniza máquina (permissão: ordens.deletar)
    Route::middleware('perm:ordens.deletar')->delete('/ordens/{id}', [OrdemServicoController::class, 'destroy'])->name('ordens.destroy');

    // GET  /ordens/{id}        → Ver detalhes da O.S. (permissão: ordens.visualizar)
    Route::middleware('perm:ordens.visualizar')->get('/ordens/{id}', [OrdemServicoController::class, 'show'])->name('ordens.show');

    // ========================================
    // HISTÓRICO DE MANUTENÇÕES
    // ========================================

    // GET  /historico          → Listar histórico com filtros (máquina, tipo, técnico, data) (permissão: historico.visualizar)
    // GET  /historico/exportar → CSV com filtros aplicados (permissão: historico.visualizar)
    // GET  /historico/maquina/{id} → Ver histórico de uma máquina + análise de reincidências (permissão: historico.visualizar)
    Route::middleware('perm:historico.visualizar')->group(function () {
        Route::get('/historico', [HistoricoController::class, 'index'])->name('historico.index');
        Route::get('/historico/exportar', [HistoricoController::class, 'exportar'])->name('historico.exportar');
        Route::get('/historico/maquina/{maquinaId}', [HistoricoController::class, 'porMaquina'])->name('historico.por-maquina');
    });

    // POST /historico          → Criar histórico manual (raro - geralmente criado via O.S.) (permissão: historico.criar)
    Route::middleware('perm:historico.criar')->post('/historico', [HistoricoController::class, 'store'])->name('historico.store');

    // DELETE /historico/{id}   → Deletar registro de histórico (permissão: historico.deletar)
    Route::middleware('perm:historico.deletar')->delete('/historico/{id}', [HistoricoController::class, 'destroy'])->name('historico.destroy');

    // GET  /historico/{id}     → Ver detalhes do registro (permissão: historico.visualizar)
    Route::middleware('perm:historico.visualizar')->get('/historico/{id}', [HistoricoController::class, 'show'])->name('historico.show');

    // ========================================
    // PERFIL DO USUÁRIO
    // ========================================

    // GET  /profile            → Editar próprio perfil + notificações (autenticado)
    // PATCH /profile           → Salvar alterações do perfil (name, email) (autenticado)
    // POST /profile/notificacoes/lidas → Marcar notificações como lidas (autenticado)
    // DELETE /profile          → Deletar própria conta (irreversível!) (autenticado)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/notificacoes/lidas', [ProfileController::class, 'markNotificationsAsRead'])->name('profile.notifications.read');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ========================================
    // GERENCIAMENTO DE ACESSO (PERMISSÕES)
    // ========================================

    // GET  /acesso             → Grid de permissões (admin vs usuario) (permissão: acesso.gerenciar)
    // POST /acesso/usuario/{user}/permissoes → Atualizar permissões individuais via AJAX (permissão: acesso.gerenciar)
    // POST /acesso/role/{role} → Atualizar permissões de um role via AJAX (permissão: acesso.gerenciar)
    // GET  /acesso/usuarios    → Ver usuários e seus roles (permissão: acesso.gerenciar)
    // PATCH /acesso/usuario/{user} → Mudar role de usuário via AJAX (permissão: acesso.gerenciar)
    Route::middleware('perm:acesso.gerenciar')->group(function () {
        Route::get('/acesso', [AccessController::class, 'index'])->name('acesso.index');
        Route::post('/acesso/usuario/{user}/permissoes', [AccessController::class, 'updateUserPermissions'])->name('acesso.updateUserPermissions');
        Route::post('/acesso/role/{role}', [AccessController::class, 'updateRole'])->name('acesso.updateRole');
        Route::get('/acesso/usuarios', [AccessController::class, 'usuarios'])->name('acesso.usuarios');
        Route::patch('/acesso/usuario/{user}', [AccessController::class, 'updateUsuario'])->name('acesso.updateUsuario');
    });

    // ========================================
    // GERENCIAMENTO DE USUÁRIOS (CRUD)
    // ========================================

    // GET  /usuarios           → Listar todos os usuários (permissão: usuarios.visualizar)
    Route::middleware('perm:usuarios.visualizar')->get('/usuarios', [UserManagementController::class, 'index'])->name('usuarios.index');

    // GET  /usuarios/criar     → Formulário de criar usuário (permissão: usuarios.criar)
    // POST /usuarios           → Salvar novo usuário (permissão: usuarios.criar)
    Route::middleware('perm:usuarios.criar')->group(function () {
        Route::get('/usuarios/criar', [UserManagementController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UserManagementController::class, 'store'])->name('usuarios.store');
    });

    // GET  /usuarios/{user}/editar → Formulário de editar usuário (permissão: usuarios.editar)
    // PUT  /usuarios/{user}    → Salvar alterações do usuário (permissão: usuarios.editar)
    Route::middleware('perm:usuarios.editar')->group(function () {
        Route::get('/usuarios/{user}/editar', [UserManagementController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{user}', [UserManagementController::class, 'update'])->name('usuarios.update');
    });

    // DELETE /usuarios/{user}  → Deletar usuário (permissão: usuarios.deletar)
    Route::middleware('perm:usuarios.deletar')->delete('/usuarios/{user}', [UserManagementController::class, 'destroy'])->name('usuarios.destroy');

    // GET  /usuarios/{user}/permissoes → Ver permissões do usuário (read-only) (permissão: usuarios.permissoes)
    Route::middleware('perm:usuarios.permissoes')->get('/usuarios/{user}/permissoes', [UserManagementController::class, 'showPermissions'])->name('usuarios.permissions');
});

require __DIR__.'/auth.php';
