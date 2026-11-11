<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="mt-1 block w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="mt-4 block">
            <label for="remember_me" class="inline-flex min-h-[44px] items-center">
                <input id="remember_me" type="checkbox" class="h-5 w-5 rounded border-2 border-line text-action focus:ring-action" name="remember">
                <span class="ms-2 text-soft">{{ __('Remember me') }}</span>
            </label>
        </div>

        <x-primary-button class="mt-2 sm:w-full">
            {{ __('Log in') }}
        </x-primary-button>

        @if (Route::has('password.request'))
            <p class="mt-3 text-center">
                <a class="inline-flex min-h-[44px] items-center font-bold text-action hover:underline" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            </p>
        @endif
    </form>
</x-guest-layout>