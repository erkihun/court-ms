<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('auth.profile.delete_account_warning') }}
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-700 active:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-fast hover:scale-[var(--scale-hover)] active:scale-[var(--scale-press)]">{{ __('Delete Account') }}</button>

    <div x-data="{ show: @json($errors->userDeletion->isNotEmpty()) }"
        x-show="show"
        x-on:open-modal.window="show = true"
        x-on:close-modal.window="show = false"
        x-on:keydown.escape.window="show = false"
        class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
        style="display: none;">
        <div class="fixed inset-0 transform " x-on:click="show = false" x-transition:enter="motion-overlay-enter"
            x-transition:enter-start="motion-fade-start"
            x-transition:enter-end="motion-fade-end"
            x-transition:leave="motion-overlay-leave"
            x-transition:leave-start="motion-fade-end"
            x-transition:leave-end="motion-fade-start">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform sm:w-full sm:mx-auto max-w-lg"
            x-transition:enter="motion-enter"
            x-transition:enter-start="motion-modal-start"
            x-transition:enter-end="motion-modal-end"
            x-transition:leave="motion-leave"
            x-transition:leave-start="motion-modal-end"
            x-transition:leave-end="motion-modal-start">
            <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                @csrf
                @method('delete')

                <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Are you sure you want to delete your account?') }}
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    {{ __('auth.profile.delete_account_confirmation_warning') }}
                </p>

                <div class="mt-6">
                    <label for="password" class="sr-only">{{ __('Password') }}</label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="mt-1 block w-3/4 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50"
                        placeholder="{{ __('Password') }}" />

                    @if ($errors->userDeletion->get('password'))
                    <div class="mt-2 text-sm text-red-600">
                        @foreach ($errors->userDeletion->get('password') as $message)
                        <p>{{ $message }}</p>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button"
                        x-on:click="show = false"
                        class="px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-400 active:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-fast hover:scale-[var(--scale-hover)] active:scale-[var(--scale-press)]">
                        {{ __('Cancel') }}
                    </button>

                    <button type="submit"
                        class="ms-3 px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-700 active:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-fast hover:scale-[var(--scale-hover)] active:scale-[var(--scale-press)]">
                        {{ __('Delete Account') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

