<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Please sign in to access the admin panel.') }}
        </div>

        <x-validation-errors class="mb-4" />

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-button class="inline-flex items-center px-4 py-2 bg-green-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150 ml-4">
                    {{ __('Log in') }}
                </x-button>
            </div>
        </form>

        @if (JoelButcher\Socialstream\Socialstream::show())
            <x-socialstream />
        @endif
    </x-authentication-card>

    @if (app()->environment('local', 'testing'))
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-blue-50 border border-blue-200 shadow-md overflow-hidden sm:rounded-lg">
        <h3 class="text-lg font-semibold text-blue-900 mb-3">Demo Credentials</h3>
        <p class="text-sm text-blue-700 mb-4">Use these credentials to test different user roles:</p>
        <div class="space-y-4">
            <div>
                <h4 class="font-medium text-blue-800 mb-2">Administrative Users</h4>
                <div class="space-y-2 text-sm">
                    <div class="bg-white p-2 rounded border">
                        <div class="font-medium text-gray-900">System Administrator</div>
                        <div class="text-gray-600">Email: <span class="font-mono">admin@liberu.co.uk</span> / Pass: <span class="font-mono">password</span></div>
                    </div>
                    <div class="bg-white p-2 rounded border">
                        <div class="font-medium text-gray-900">Maintenance Manager</div>
                        <div class="text-gray-600">Email: <span class="font-mono">manager@liberu.co.uk</span> / Pass: <span class="font-mono">password</span></div>
                    </div>
                </div>
            </div>
            <div>
                <h4 class="font-medium text-blue-800 mb-2">Maintenance Technicians</h4>
                <div class="space-y-2 text-sm">
                    <div class="bg-white p-2 rounded border">
                        <div class="text-gray-600">john.smith@liberu.co.uk / sarah.johnson@liberu.co.uk / mike.wilson@liberu.co.uk</div>
                        <div class="text-gray-600">Password: <span class="font-mono">password</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
            <p class="text-xs text-yellow-800"><strong>Note:</strong> Demo credentials — local/testing environment only.</p>
        </div>
    </div>
    @endif
</x-guest-layout>
