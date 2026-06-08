<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * CONTROLLER: ProfileController
 *
 * Responsável pela edição do perfil do usuário autenticado.
 * Funcionalidades:
 * - Visualizar perfil e notificações
 * - Editar name e email
 * - Marcar notificações como lidas
 * - Deletar conta própria
 */

class ProfileController extends Controller
{
    /**
     * FUNÇÃO: edit(Request $request)
     * ENTRADA: Request autenticada
     * PROCESSAMENTO:
     *   1. Busca usuário autenticado
     *   2. Busca últimas 10 notificações
     *   3. Conta notificações não lidas
     * SAÍDA: View de edição de perfil com notificações
     * USO: GET /profile
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'notifications' => $user->notifications()->latest()->limit(10)->get(),
            'unreadNotificationsCount' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * FUNÇÃO: update(Request $request)
     * ENTRADA:
     *   - name: novo nome
     *   - email: novo email (unique)
     * PROCESSAMENTO:
     *   1. Valida name e email
     *   2. Atualiza dados do usuário
     *   3. Se email mudou: seta email_verified_at=null (requer reverificação)
     *   4. Salva no banco
     * SAÍDA: Redirecionamento de volta ao perfil com mensagem de sucesso
     * USO: PATCH /profile
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->fill($request->only('name', 'email'));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * FUNÇÃO: markNotificationsAsRead(Request $request)
     * ENTRADA: Request autenticada
     * PROCESSAMENTO:
     *   1. Busca todas as notificações não lidas do usuário
     *   2. Atualiza read_at = now() para todas
     * SAÍDA: Redirecionamento de volta ao perfil com status
     * USO: POST /profile/notificacoes/lidas
     */
    public function markNotificationsAsRead(Request $request): RedirectResponse
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return Redirect::route('profile.edit')->with('status', 'notifications-read');
    }

    /**
     * FUNÇÃO: destroy(Request $request)
     * ENTRADA: Request autenticada + password (para confirmação)
     * PROCESSAMENTO:
     *   1. Valida que password atual está correta
     *   2. Faz logout do usuário
     *   3. Deleta a conta (cascade deleta user_permissions)
     *   4. Invalida sessão
     *   5. Regenera token CSRF
     * SAÍDA: Redirecionamento para home (/)
     * USO: DELETE /profile
     * NOTA: IRREVERSÍVEL — usuário é deletado permanentemente
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
