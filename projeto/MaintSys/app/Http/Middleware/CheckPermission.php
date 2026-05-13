<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $perm): Response
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission($perm)) {
            abort(403, "Acesso negado: permissão necessária '$perm'.");
        }

        return $next($request);
    }
}
