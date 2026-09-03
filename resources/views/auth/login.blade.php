<x-guest-layout>
    <div class="mb-8"><p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-600">Bienvenido</p><h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Inicia sesión</h2><p class="mt-2 text-sm text-slate-500">Accede a tu registro de videos técnicos.</p></div>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Correo electrónico" class="font-medium text-slate-700" />
            <x-text-input id="email" class="mt-2 block w-full border-slate-300 py-3 focus:border-red-500 focus:ring-red-500" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Contraseña" class="font-medium text-slate-700" />

            <x-text-input id="password" class="mt-2 block w-full border-slate-300 py-3 focus:border-red-500 focus:ring-red-500"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-slate-600">Recordarme</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3 bg-red-600 px-5 py-3 hover:bg-red-700 focus:bg-red-700">
                Entrar al registro
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
