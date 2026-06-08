<?php

/**
 * ROTAS: auth.php — Autenticação
 *
 * Rotas de login, logout, recuperação de senha e verificação de email.
 * Organizadas em dois grupos:
 * - Rotas públicas (guest: login não autenticado)
 * - Rotas autenticadas (auth: login obrigatório)
 */

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// ========================================
// ROTAS PÚBLICAS (SEM AUTENTICAÇÃO)
// ========================================

Route::middleware('guest')->group(function () {

    // ⚠️ AUTO-CADASTRO DESABILITADO
    // Usuários devem ser criados por administradores via GET /usuarios/criar
    // Descomentar abaixo para habilitar auto-registro
    // Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    // Route::post('register', [RegisteredUserController::class, 'store']);

    // GET  /login              → Mostrar formulário de login
    // POST /login              → Autenticar usuário com email + password
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // GET  /forgot-password    → Mostrar formulário "Esqueci a senha"
    // POST /forgot-password    → Enviar link de reset por email
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // GET  /reset-password/{token} → Mostrar formulário de redefinir senha (com token do email)
    // POST /reset-password     → Salvar nova senha no banco
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

// ========================================
// ROTAS AUTENTICADAS
// ========================================

Route::middleware('auth')->group(function () {

    // Email Verification (raramente usado, email_verified_at é setado no create)
    // GET  /verify-email       → Mostrar página para verificar email (se email_verified_at = null)
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    // GET  /verify-email/{id}/{hash} → Link do email para verificar (assinado, rate-limitado a 6/min)
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    // POST /email/verification-notification → Reenviar email de verificação (rate-limitado a 6/min)
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // GET  /confirm-password   → Mostrar formulário de confirmação de senha (antes de ações sensíveis)
    // POST /confirm-password   → Confirmar senha do usuário
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // PUT  /password           → Mudar própria senha (autenticado)
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // POST /logout             → Fazer logout (apaga sessão, token CSRF, redirect para home)
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
