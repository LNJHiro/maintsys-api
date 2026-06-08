<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MIDDLEWARE: CheckAdmin
 *
 * Verifica se o usuário é admin (role = 'admin' ou 'admin_master').
 * Bloqueio simples: apenas admins podem passar.
 *
 * Usado em: middleware('admin') — geralmente para seções de administração
 */

class CheckAdmin
{
    /**
     * FUNÇÃO: handle(Request $request, Closure $next)
     * ENTRADA: Request autenticada
     * PROCESSAMENTO:
     *   1. Busca usuário autenticado
     *   2. Chama $user->isAdmin() que verifica role
     *   3. Se não é admin: abort(403)
     * SAÍDA: Response da rota, ou erro 403
     * LÓGICA: Bloqueio binário — admin ou não
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Acesso negado. Apenas administradores podem acessar esta área.');
        }

        return $next($request);
    }
}
