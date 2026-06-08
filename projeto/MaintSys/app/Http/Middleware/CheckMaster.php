<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MIDDLEWARE: CheckMaster
 *
 * Verifica se o usuário é admin_master (acesso total irrestrito).
 * Bloqueio muito restritivo: apenas master pode passar.
 *
 * Usado em: middleware('master') — para operações críticas
 */

class CheckMaster
{
    /**
     * FUNÇÃO: handle(Request $request, Closure $next)
     * ENTRADA: Request autenticada
     * PROCESSAMENTO:
     *   1. Busca usuário autenticado
     *   2. Chama $user->isMaster() que verifica role == 'admin_master'
     *   3. Se não é master: abort(403)
     * SAÍDA: Response da rota, ou erro 403
     * LÓGICA: Bloqueio ultra-restritivo — só master (não admin comum)
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isMaster()) {
            abort(403, 'Acesso negado. Apenas Admin Master pode acessar esta área.');
        }

        return $next($request);
    }
}
