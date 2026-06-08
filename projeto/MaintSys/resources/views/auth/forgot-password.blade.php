{{-- Componente de layout para páginas sem autenticação (guest layout) --}}
<x-guest-layout>
    {{-- Texto explicativo exibido acima do formulário descrevendo o processo de recuperação --}}
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    {{-- Componente que exibe mensagens de status da sessão (ex.: "Link enviado com sucesso") --}}
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- Formulário enviado via POST para a rota que envia o e-mail de redefinição --}}
    <form method="POST" action="{{ route('password.email') }}">
        {{-- Token CSRF obrigatório para proteção da requisição --}}
        @csrf

        {{-- Grupo do campo de e-mail para recuperação de senha --}}
        <!-- Email Address -->
        <div>
            {{-- Componente de rótulo do campo de e-mail --}}
            <x-input-label for="email" :value="__('Email')" />
            {{-- Componente de input de e-mail obrigatório com foco automático --}}
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            {{-- Componente que exibe mensagens de erro de validação para o campo email --}}
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Container do botão de envio alinhado à direita --}}
        <div class="flex items-center justify-end mt-4">
            {{-- Componente de botão primário que envia o link de redefinição por e-mail --}}
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout> {{-- fim do layout guest --}}
