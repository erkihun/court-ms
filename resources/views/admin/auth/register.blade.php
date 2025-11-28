<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- National ID -->
        <div class="mt-4">
            <x-input-label for="national_id_number" :value="__('National ID')" />
            <input id="national_id_number"
                name="national_id_number"
                value="{{ old('national_id_number') }}"
                required
                inputmode="text"
                autocomplete="off"
                maxlength="19"
                pattern="[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}\s[A-Za-z0-9]{4}"
                title="{{ __('auth.national_id_format') }}"
                placeholder="{{ __('auth.national_id_placeholder') }}"
                aria-describedby="national_id_help"
                class="block mt-1 w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-gray-900" />
            <p id="national_id_help" class="text-xs text-gray-500 mt-1">{{ __('auth.national_id_hint') }}</p>
            <x-input-error :messages="$errors->get('national_id_number')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                type="password"
                name="password"
                required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                type="password"
                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    {{-- National ID auto-formatter --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const idInput = document.querySelector('input[name="national_id_number"]');
            if (idInput) {
                const format = (val) => {
                    const cleaned = (val || '')
                        .replace(/[^A-Za-z0-9]/g, '')
                        .slice(0, 16)
                        .toUpperCase();
                    const parts = cleaned.match(/.{1,4}/g) || [];
                    return parts.join(' ');
                };

                // Format prefilled value (e.g., old input)
                idInput.value = format(idInput.value);

                idInput.addEventListener('input', (e) => {
                    const after = format(e.target.value);
                    e.target.value = after;
                });

                idInput.addEventListener('blur', () => {
                    idInput.value = format(idInput.value);
                });
            }
        });
    </script>
</x-guest-layout>
