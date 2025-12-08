@php
$initialRole = request('login_as') === 'respondent' ? 'respondent' : 'applicant';
// Always show applicant nav on the login page
session()->forget('acting_as_respondent');
@endphp

<x-applicant-layout title="Apply - Login" hide-footer="true" :as-respondent-nav="false">
    <div class="min-h-[70vh] flex items-center justify-center">
        <form method="POST" action="{{ route('applicant.login.submit') }}" class="bg-white p-6 rounded-lg border space-y-4 w-full max-w-md shadow">
            @csrf
            <div class="text-center space-y-1">
                <h1 class="text-2xl font-semibold">Applicant Login</h1>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600" data-panel-label>
                    {{ $initialRole === 'respondent' ? 'Respondent Login Panel' : 'Applicant Login Panel' }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700" for="login_as">Login as</label>
                <select id="login_as" name="login_as"
                    class="mt-1 w-full px-3 py-2 rounded border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                    <option value="applicant" {{ $initialRole === 'applicant' ? 'selected' : '' }}>Applicant</option>
                    <option value="respondent" {{ $initialRole === 'respondent' ? 'selected' : '' }}>Respondent</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input name="email" type="email" value="{{ old('email') }}" class="mt-1 w-full px-3 py-2 border rounded" required>
                @error('email') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Password</label>
                <input name="password" type="password" class="mt-1 w-full px-3 py-2 border rounded" required>
                @error('password') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" class="rounded border-slate-300">
                Remember me
            </label>
            <div class="flex items-center gap-3 pt-2">
                <button class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Sign in</button>
                <a href="{{ route('applicant.register') }}" class="text-sm text-blue-700 hover:underline">Create an account</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const roleSelect = document.getElementById('login_as');
            const panelLabel = document.querySelector('[data-panel-label]');
            if (roleSelect && panelLabel) {
                const update = () => {
                    panelLabel.textContent = roleSelect.value === 'respondent'
                        ? 'Respondent Login Panel'
                        : 'Applicant Login Panel';
                };
                roleSelect.addEventListener('change', update);
                update();
            }
        });
    </script>
</x-applicant-layout>
