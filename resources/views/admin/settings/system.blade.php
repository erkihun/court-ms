{{-- resources/views/settings/system.blade.php --}}
<x-admin-layout title="System Settings">
    @section('page_header','System Settings')

    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Page intro --}}
        <div class="bg-gradient-to-r from-indigo-600 via-indigo-500 to-sky-500 rounded-2xl px-6 py-5 text-white shadow-md flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold tracking-tight">System Settings</h1>
                <p class="text-sm text-indigo-100 mt-1 leading-snug">
                    Manage core information, branding, contact details, and system-wide preferences.
                </p>
            </div>
            <div class="hidden md:flex items-center">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs bg-white/10 backdrop-blur-sm border border-white/20 shadow-sm">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                    Settings take effect after saving
                </span>
            </div>
        </div>

        {{-- Flash message --}}
        @if(session('ok'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 flex items-start gap-3 text-sm text-emerald-800 shadow-sm">
            <svg class="h-5 w-5 mt-0.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <div class="font-semibold">Settings saved</div>
                <p class="text-xs mt-1">{{ session('ok') }}</p>
            </div>
        </div>
        @endif

        {{-- Validation errors --}}
        @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 mt-0.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3m0 3h.01M4.93 4.93l14.14 14.14M12 4a8 8 0 100 16 8 8 0 000-16z" />
                </svg>
                <div>
                    <div class="font-semibold mb-1">Please correct the following:</div>
                    <ul class="list-disc list-inside space-y-0.5 text-xs">
                        @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        {{-- Settings Form --}}
        <form method="POST" action="{{ route('settings.system.update') }}" enctype="multipart/form-data"
            class="space-y-10 bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-7 md:px-8 md:py-8">
            @csrf

            {{-- GENERAL --}}
            <section class="space-y-5">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-inner">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 4.354a4 4 0 110 5.292M15 21H5v-1a6 6 0 0112 0v1zm0 0h4v-1a6 6 0 00-9-5.197" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">General</h2>
                        <p class="text-sm text-gray-500">System identity and display information.</p>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-5">
                    {{-- System name --}}
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700 flex items-center justify-between">
                            <span>System Name <span class="text-red-500">*</span></span>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                Appears in browser titles
                            </span>
                        </label>
                        <input name="app_name" value="{{ old('app_name', $settings->app_name) }}" required
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 shadow-sm
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>

                    {{-- Short name --}}
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700 flex items-center justify-between">
                            <span>Short Name</span>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                Used in sidebar
                            </span>
                        </label>
                        <input name="short_name" value="{{ old('short_name', $settings->short_name) }}"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 shadow-sm
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                </div>

                {{-- Maintenance Mode --}}
                <div class="mt-3 rounded-xl bg-gray-50 border border-dashed border-gray-300 px-4 py-3 flex items-start gap-3">
                    <input type="checkbox" name="maintenance_mode" value="1"
                        @checked(old('maintenance_mode', $settings->maintenance_mode))
                    class="mt-0.5 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">

                    <div>
                        <label class="font-medium text-gray-800">Enable maintenance mode</label>
                        <p class="text-xs text-gray-500">
                            Shows a maintenance message to public users.
                        </p>
                    </div>
                </div>
            </section>

            {{-- BRANDING --}}
            <section class="space-y-5 border-t border-gray-100 pt-7">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M3 7v4a1 1 0 001 1h3m10-5h3a1 1 0 011 1v4m-7 4h4l1 4H7l1-4h4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Branding</h2>
                        <p class="text-sm text-gray-500">Upload images used for identity and layout.</p>
                    </div>
                </div>


                {{-- Banner --}}
                <div class="grid md:grid-cols-[1.5fr_1fr] gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Banner</label>
                        <div class="border border-gray-200 bg-gray-50 rounded-xl px-4 py-3 shadow-inner space-y-2">
                            <input type="file" name="banner"
                                class="text-sm text-gray-700 file:bg-indigo-50 file:text-indigo-700
                                       file:px-4 file:py-2 file:rounded-lg file:border-0
                                       hover:file:bg-indigo-100">
                            <p class="text-xs text-gray-500">Wide header image (max 3MB)</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        @if($settings->banner_path)
                        <p class="text-[12px] uppercase text-gray-500">Current Banner</p>
                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                            <img src="{{ asset('storage/'.$settings->banner_path) }}" class="w-full max-h-40 object-cover">
                        </div>
                        @else
                        <p class="text-xs text-gray-500 italic">No banner uploaded</p>
                        @endif
                    </div>
                </div>


            </section>
            {{-- BRANDING --}}
            <section class="space-y-5 border-t border-gray-100 pt-7">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M3 7v4a1 1 0 001 1h3m10-5h3a1 1 0 011 1v4m-7 4h4l1 4H7l1-4h4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Branding</h2>
                        <p class="text-sm text-gray-500">Upload images used throughout the system.</p>
                    </div>
                </div>

                {{-- 3-Column Layout --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- LOGO --}}
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-4 space-y-3">
                        <label class="text-sm font-medium text-gray-700">Logo</label>
                        <input type="file" name="logo"
                            class="text-sm text-gray-700 file:bg-indigo-50 file:text-indigo-700
                          file:px-4 file:py-2 file:rounded-lg file:border-0 hover:file:bg-indigo-100">

                        <p class="text-xs text-gray-500">PNG, JPG, SVG, WEBP — Max 2MB</p>

                        @if($settings->logo_path)
                        <div>
                            <p class="text-[11px] uppercase text-gray-500 mb-1">Current</p>
                            <div class="border border-gray-200 bg-white rounded-xl shadow-sm inline-flex px-3 py-3">
                                <img src="{{ asset('storage/'.$settings->logo_path) }}" class="h-12 object-contain">
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- FAVICON --}}
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-4 space-y-3">
                        <label class="text-sm font-medium text-gray-700">Favicon</label>
                        <input type="file" name="favicon"
                            class="text-sm text-gray-700 file:bg-indigo-50 file:text-indigo-700
                          file:px-4 file:py-2 file:rounded-lg file:border-0 hover:file:bg-indigo-100">

                        <p class="text-xs text-gray-500">PNG or ICO — Max 512KB</p>

                        @if($settings->favicon_path)
                        <div>
                            <p class="text-[11px] uppercase text-gray-500 mb-1">Current</p>
                            <div class="border border-gray-200 bg-white rounded-xl shadow-sm inline-flex px-3 py-3">
                                <img src="{{ asset('storage/'.$settings->favicon_path) }}" class="h-8 w-8 object-contain">
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- OFFICIAL SEAL --}}
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-4 space-y-3">
                        <label class="text-sm font-medium text-gray-700">Official Seal</label>
                        <input type="file" name="seal"
                            class="text-sm text-gray-700 file:bg-indigo-50 file:text-indigo-700
                          file:px-4 file:py-2 file:rounded-lg file:border-0 hover:file:bg-indigo-100">

                        <p class="text-xs text-gray-500">Transparent PNG — Max 1MB</p>

                        @if($settings->seal_path)
                        <div>
                            <p class="text-[11px] uppercase text-gray-500 mb-1">Current</p>
                            <div class="border border-gray-200 bg-white rounded-xl shadow-sm inline-flex px-3 py-3">
                                <img src="{{ asset('storage/'.$settings->seal_path) }}" class="h-14 w-14 object-contain">
                            </div>
                        </div>
                        @endif
                    </div>

                </div>
            </section>

            {{-- ABOUT --}}
            <section class="space-y-5 border-t border-gray-100 pt-7">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 110-16 8 8 0 010 16z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">About</h2>
                        <p class="text-sm text-gray-500">Displayed on public pages.</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">About the System / Court</label>
                    <textarea name="about" rows="5"
                        class="w-full px-3 py-3 rounded-lg border border-gray-300 shadow-sm text-sm
                               focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('about', $settings->about) }}</textarea>
                </div>
            </section>

            {{-- CONTACT --}}
            <section class="space-y-5 border-t border-gray-100 pt-7">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-inner">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684L10.6 6H19a2 2 0 012 2v9a2 2 0 01-2 2h-3m-4 0H5a2 2 0 01-2-2V5z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Contact Information</h2>
                        <p class="text-sm text-gray-500">Used in email footers & contact pages.</p>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Contact Email</label>
                        <input type="email" name="contact_email"
                            value="{{ old('contact_email', $settings->contact_email) }}"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 shadow-sm
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Contact Phone</label>
                        <input name="contact_phone" value="{{ old('contact_phone', $settings->contact_phone) }}"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 shadow-sm
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                </div>
            </section>

            {{-- ACTION BUTTON --}}
            <div class="pt-5 border-t border-gray-200 flex justify-end">
                <button
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg px-5 py-2.5 shadow-sm transition focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Settings
                </button>
            </div>

        </form>
    </div>
</x-admin-layout>