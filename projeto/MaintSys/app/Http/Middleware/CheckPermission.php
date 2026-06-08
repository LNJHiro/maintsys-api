<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MIDDLEWARE: CheckPermission
 *
 * Verifica se o usuário autenticado tem uma permissão específica.
 * Usado em: middleware('perm:modulo.acao') nas rotas
 *
 * Exemplo de uso:
 *   Route::post('/ordens', ...)->middleware('perm:ordens.criar');
 */

class CheckPermission
{
    /**
     * FUNÇÃO: handle(Request $request, Closure $next, string $perm)
     * ENTRADA: $perm = nome da permissão (ex: 'ordens.criar')
     * PROCESSAMENTO:
     *   1. Busca usuário autenticado
     *   2. Chama $user->hasPermission($perm)
     *   3. Se false: abort(403)
     *   4. Se true: continua
     * SAÍDA: Response da rota, ou erro 403
     * LÓGICA: Bloqueia acesso se não tem a permissão exigida
     */
    public function handle(Request $request, Closure $next, string $perm): Response
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission($perm)) {
            abort(403, "Acesso negado: permissão necessária '$perm'.");
        }

        return $next($request);
    }
}
