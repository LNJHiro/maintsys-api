<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MIDDLEWARE: CheckAdminAccess
 *
 * Verifica se o usuário tem a permissão 'acesso.gerenciar'.
 * Permite admin com permissão granular.
 *
 * Usado em: middleware('acesso.gerenciar') — para gerenciar permissões
 *
 * Diferença do CheckAdmin:
 * - CheckAdmin: role = admin (binário)
 * - CheckAdminAccess: permissão 'acesso.gerenciar' (granular)
 */

class CheckAdminAccess
{
    /**
     * FUNÇÃO: handle(Request $request, Closure $next)
     * ENTRADA: Request autenticada
     * PROCESSAMENTO:
     *   1. Busca usuário autenticado
     *   2. Chama $user->hasPermission('acesso.gerenciar')
     *   3. Se não tem: abort(403)
     * SAÍDA: Response da rota, ou erro 403
     * LÓGICA: Bloqueio por permissão (não por role)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || !$user->hasPermission('acesso.gerenciar')) {
            abort(403, 'Apenas administradores têm acesso a esta seção.');
        }

        return $next($request);
    }
}
