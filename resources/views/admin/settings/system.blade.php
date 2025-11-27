{{-- resources/views/settings/system.blade.php --}}
<x-admin-layout title="System Settings">
    @section('page_header','System Settings')

    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Page intro --}}
        <div class="bg-gradient-to-r from-indigo-600 via-indigo-500 to-sky-500 rounded-2xl px-5 py-4 text-white shadow-sm flex items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold">System Settings</h1>
                <p class="text-xs md:text-sm text-indigo-100 mt-1">
                    Manage core information, branding, and contact details used across the system.
                </p>
            </div>
            <div class="hidden md:flex items-center gap-2 text-xs text-indigo-100">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-black/10 backdrop-blur">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                    Changes apply system-wide after you save
                </span>
            </div>
        </div>

        {{-- Flash message --}}
        @if(session('ok'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 flex items-start gap-3 text-sm text-emerald-800">
            <div class="mt-0.5">
                <svg class="h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="font-medium">Saved successfully</div>
                <p class="mt-0.5 text-xs md:text-sm">{{ session('ok') }}</p>
            </div>
        </div>
        @endif

        {{-- Validation errors --}}
        @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 mt-0.5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 9v3m0 3h.01M4.93 4.93l14.14 14.14M12 4a8 8 0 100 16 8 8 0 000-16z" />
                </svg>
                <div>
                    <div class="font-semibold mb-1">Please fix the following:</div>
                    <ul class="list-disc list-inside space-y-0.5 text-xs md:text-sm">
                        @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <form method="POST"
            action="{{ route('settings.system.update') }}"
            enctype="multipart/form-data"
            class="space-y-8 bg-white rounded-2xl border border-gray-200 shadow-sm px-5 py-6 md:px-7 md:py-7">
            @csrf

            {{-- GENERAL --}}
            <section class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <div
                            class="h-9 w-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-inner">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H5v-1a6 6 0 0112 0v1zm0 0h4v-1a6 6 0 00-9-5.197" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base md:text-lg font-semibold text-gray-900">General</h2>
                            <p class="text-xs text-gray-500">
                                Basic system identity used in headers, titles, and navigation.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between gap-2">
                            <label class="block text-sm font-medium text-gray-700">
                                System Name <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">
                                Shown in page titles
                            </span>
                        </div>
                        <input
                            name="app_name"
                            value="{{ old('app_name', $settings->app_name) }}"
                            required
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white text-gray-900
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder:text-gray-400 text-sm">
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between gap-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Short Name
                            </label>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">
                                Sidebar / header
                            </span>
                        </div>
                        <input
                            name="short_name"
                            value="{{ old('short_name', $settings->short_name) }}"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white text-gray-900
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder:text-gray-400 text-sm">
                    </div>
                </div>

                <div
                    class="mt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 rounded-xl bg-gray-50 border border-dashed border-gray-200 px-3.5 py-3.5">
                    <label class="inline-flex items-center gap-3 text-sm text-gray-800">
                        <span class="relative inline-flex items-center">
                            {{-- same checkbox, only styled better --}}
                            <input type="checkbox"
                                name="maintenance_mode"
                                value="1"
                                @checked(old('maintenance_mode', $settings->maintenance_mode))
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </span>
                        <span class="flex flex-col">
                            <span class="font-medium">Enable maintenance mode</span>
                            <span class="text-xs text-gray-500">
                                Use this flag to show a maintenance message on the public side when needed.
                            </span>
                        </span>
                    </label>
                </div>
            </section>

            {{-- BRANDING --}}
            <section class="space-y-5 pt-5 border-t border-gray-100">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <div
                            class="h-9 w-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-inner">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M3 7v4a1 1 0 001 1h3m10-5h3a1 1 0 011 1v4m-7 4h4l1 4H7l1-4h4" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base md:text-lg font-semibold text-gray-900">Branding</h2>
                            <p class="text-xs text-gray-500">
                                Logo and favicon used in the admin panel and public pages.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Logo --}}
                <div class="grid md:grid-cols-[minmax(0,1.4fr)_minmax(0,0.8fr)] gap-4 md:gap-6 items-center">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Logo
                        </label>
                        <div
                            class="border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50/60 flex flex-col gap-2">
                            <input type="file" name="logo"
                                class="block w-full text-xs md:text-sm text-gray-700
                                          file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                                          file:text-xs md:file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100">
                            <p class="text-xs text-gray-500">
                                PNG, JPG, SVG, WEBP &mdash; up to <span class="font-medium">2MB</span>.
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        @if($settings->logo_path)
                        <div class="text-center space-y-2">
                            <div class="text-[11px] uppercase tracking-wide text-gray-500">
                                Current Logo
                            </div>
                            <div class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-xs">
                                <img src="{{ asset('storage/'.$settings->logo_path) }}"
                                    alt="Logo"
                                    class="h-12 w-auto object-contain">
                            </div>
                        </div>
                        @else
                        <div class="text-xs text-gray-500 italic">
                            No logo uploaded yet.
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Banner --}}
                <div class="grid md:grid-cols-[minmax(0,1.4fr)_minmax(0,0.8fr)] gap-4 md:gap-6 items-start">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Banner (optional)
                        </label>
                        <div
                            class="border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50/60 flex flex-col gap-2">
                            <input type="file" name="banner"
                                class="block w-full text-xs md:text-sm text-gray-700
                                          file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                                          file:text-xs md:file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100">
                            <p class="text-xs text-gray-500">
                                Wide image for headers; PNG, JPG, WEBP up to <span class="font-medium">3MB</span>.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        @if($settings->banner_path ?? false)
                        <div class="text-[11px] uppercase tracking-wide text-gray-500">Current Banner</div>
                        <div class="rounded-xl border border-gray-200 bg-white shadow-xs overflow-hidden">
                            <img src="{{ asset('storage/'.$settings->banner_path) }}"
                                alt="Banner"
                                class="w-full max-h-40 object-cover">
                        </div>
                        @else
                        <div class="text-xs text-gray-500 italic">
                            No banner uploaded yet.
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Favicon --}}
                <div class="grid md:grid-cols-[minmax(0,1.4fr)_minmax(0,0.8fr)] gap-4 md:gap-6 items-center">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Favicon
                        </label>
                        <div
                            class="border border-gray-200 rounded-xl px-3 py-2.5 bg-gray-50/60 flex flex-col gap-2">
                            <input type="file" name="favicon"
                                class="block w-full text-xs md:text-sm text-gray-700
                                          file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                                          file:text-xs md:file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100">
                            <p class="text-xs text-gray-500">
                                PNG or ICO up to <span class="font-medium">512KB</span>.
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        @if($settings->favicon_path)
                        <div class="text-center space-y-2">
                            <div class="text-[11px] uppercase tracking-wide text-gray-500">
                                Current Favicon
                            </div>
                            <div
                                class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-3 shadow-xs">
                                <img src="{{ asset('storage/'.$settings->favicon_path) }}"
                                    alt="Favicon"
                                    class="h-8 w-8 object-contain">
                            </div>
                        </div>
                        @else
                        <div class="text-xs text-gray-500 italic">
                            No favicon uploaded yet.
                        </div>
                        @endif
                    </div>
                </div>
            </section>

            {{-- ABOUT --}}
            <section class="space-y-4 pt-5 border-t border-gray-100">
                <div class="flex items-center gap-2">
                    <div
                        class="h-9 w-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-inner">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 110-16 8 8 0 010 16z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base md:text-lg font-semibold text-gray-900">About Us</h2>
                        <p class="text-xs text-gray-500">
                            Description of the system / court that can be displayed on a public “About” page.
                        </p>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        About the System / Court
                    </label>
                    <textarea
                        name="about"
                        rows="5"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white text-gray-900
                               focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm leading-relaxed">{{ old('about', $settings->about) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        This text is usually shown on the public website’s About page.
                    </p>
                </div>
            </section>

            {{-- CONTACT --}}
            <section class="space-y-4 pt-5 border-t border-gray-100">
                <div class="flex items-center gap-2">
                    <div
                        class="h-9 w-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-inner">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684L10.6 6H19a2 2 0 012 2v9a2 2 0 01-2 2h-3m-4 0H5a2 2 0 01-2-2V5z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base md:text-lg font-semibold text-gray-900">Contact Information</h2>
                        <p class="text-xs text-gray-500">
                            Details used on contact pages and email footers.
                        </p>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Contact Email
                        </label>
                        <input
                            type="email"
                            name="contact_email"
                            value="{{ old('contact_email', $settings->contact_email) }}"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white text-gray-900
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm placeholder:text-gray-400">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Contact Phone
                        </label>
                        <input
                            name="contact_phone"
                            value="{{ old('contact_phone', $settings->contact_phone) }}"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 bg-white text-gray-900
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm placeholder:text-gray-400">
                    </div>
                </div>
            </section>

            {{-- ACTIONS --}}
            <div class="pt-5 border-t border-gray-100 flex items-center justify-end">
                <button
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm
                           hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Save Settings</span>
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
