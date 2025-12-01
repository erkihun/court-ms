@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
    // Localized title via group-only keys
    $t = __('app.' . $title);
    @endphp
    <title>{{ $t }} | {{ $systemSettings->app_name ?? config('app.name','Laravel') }}</title>
    <style>
        [x-cloak] {
            display: none !important
        }

        /* Fallbacks in case Tailwind build misses arbitrary transition props */
        .transition-size {
            transition: width .3s ease-in-out
        }

        .transition-padding {
            transition: padding-left .3s ease-in-out
        }

        .icon-green {
            color: #059669 !important;
        }
    </style>
    @vite(['resources/css/app.css','resources/js/app.js'])
    @stack('styles')
</head>

<body x-data="layoutState()" x-init="init()" class="min-h-screen flex bg-gray-50 text-gray-900">

    {{-- Sidebar (mobile slide-in + desktop collapsible) --}}
    @php
    use Illuminate\Support\Facades\Route;

    // Guard route usage to avoid "Route [...] not defined" exceptions
    $hasDashboard = Route::has('dashboard');
    $hasAppeals = Route::has('appeals.index');
    $hasCases = Route::has('cases.index');
    $hasCaseTypes = Route::has('case-types.index');
    $hasUsers = Route::has('users.index');
    $hasPermissions = Route::has('permissions.index');
    $hasRoles = Route::has('roles.index');
    $hasNotifIndex = Route::has('admin.notifications.index');
    $hasNotifMarkAll = Route::has('admin.notifications.markAll');
    $hasNotifMarkOne = Route::has('admin.notifications.markOne');
    $hasProfileEdit = Route::has('profile.edit');
    $hasLogout = Route::has('logout');
    $hasLangSwitch = Route::has('language.switch');
    $hasSystemSettings = Route::has('settings.system.edit');
    $hasTerms = Route::has('terms.index');
    $hasLetterTemplates = Route::has('letter-templates.index');
    $hasLetterComposer = Route::has('letters.compose');
    $hasLetters = Route::has('letters.index');
    $canManageTemplates = $hasLetterTemplates && auth()->user()?->hasPermission('templates.manage');
    $canViewLetters = $hasLetters && auth()->user()?->hasPermission('cases.edit');
    $canComposeLetters = $hasLetterComposer && auth()->user()?->hasPermission('cases.edit');
    $canManageUsers = $hasUsers && auth()->user()?->hasPermission('users.manage');
    $canManagePermissions = $hasPermissions && auth()->user()?->hasPermission('permissions.manage');
    $canManageRoles = $hasRoles && auth()->user()?->hasPermission('roles.manage');
    $letterTemplatesActive = request()->routeIs('letter-templates.*');
    $lettersActive = request()->routeIs('letters.index') || request()->routeIs('letters.show');
    $composeActive = request()->routeIs('letters.compose');
    $letterMenuOpen = $letterTemplatesActive || $lettersActive || $composeActive;
    $usersActive = request()->routeIs('users.*');
    $permissionsActive = request()->routeIs('permissions.*');
    $rolesActive = request()->routeIs('roles.*');
    $userControlOpen = $usersActive || $permissionsActive || $rolesActive;
    @endphp

    <aside
        class="fixed md:static z-40 inset-y-0 left-0
               transform transition-transform duration-300 ease-out
               -translate-x-full md:translate-x-0
               flex flex-col bg-blue-900 dark:bg-gray-800 border-r border-blue-800
               w-72 md:[transition-property:width] md:duration-300 md:ease-in-out transition-size"
        :class="{
            'translate-x-0': sidebar,
            'md:w-20': compact,
            'md:w-64': !compact
        }"
        aria-label="{{ __('app.Sidebar') }}">

        {{-- Brand / collapse toggle row --}}
        <div class="flex items-center justify-between gap-2 px-4 py-4 border-b border-blue-800">
            <div class="flex items-center gap-2">
                <a href="{{ $hasDashboard ? route('dashboard') : url('/') }}" aria-label="{{ __('app.Dashboard') }}">
                    {{-- Full name (shown when NOT compact) --}}
                    <span class="text-white text-xl font-bold truncate origin-left"
                        x-show="!compact"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-x-1"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-x-0"
                        x-transition:leave-end="opacity-0 -translate-x-1">
                        {{ $systemSettings->app_name ?? config('app.name','Laravel') }}
                    </span>

                    {{-- Short name (shown when compact) --}}
                    <span class="text-white text-xl font-bold truncate origin-left"
                        x-show="compact"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-x-1"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-x-0"
                        x-transition:leave-end="opacity-0 -translate-x-1">
                        {{ $systemSettings->short_name ?? 'CMS' }}
                    </span>
                </a>
            </div>

            {{-- Close on mobile --}}
            <button type="button"
                class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-md text-blue-100 hover:bg-orange-800"
                @click="sidebar=false"
                aria-label="{{ __('app.Close sidebar') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 p-3 space-y-2 overflow-y-auto">
            {{-- Dashboard --}}
            @if($hasDashboard)
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md transition
                      {{ request()->routeIs('dashboard') ? 'bg-orange-600 text-white' : 'hover:bg-orange-600 text-blue-100 hover:text-white' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-5 w-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <span class="truncate origin-left"
                    x-show="!compact"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-x-1"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-1">
                    {{ __('app.Dashboard') }}
                </span>
            </a>
            @endif

            {{-- Appeals --}}
            @if($hasAppeals && auth()->user()?->hasPermission('appeals.view'))
            <a href="{{ route('appeals.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('appeals.*') ? 'bg-orange-600 text-white' : 'hover:bg-orange-800 text-blue-100 hover:text-white' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-5 w-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <span class="truncate origin-left"
                    x-show="!compact"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-x-1"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-1">
                    {{ __('app.Appeals') }}
                </span>
            </a>
            @endif

            {{-- Cases --}}
            @if($hasCases && auth()->user()?->hasPermission('cases.view'))
            <a href="{{ route('cases.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('cases.*') ? 'bg-orange-600 text-white' : 'hover:bg-orange-800 text-blue-100 hover:text-white' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-5 w-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <span class="truncate origin-left"
                    x-show="!compact"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-x-1"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-1">
                    {{ __('app.Cases') }}
                </span>
            </a>
            @endif

            {{-- Case Types --}}
            @if($hasCaseTypes && auth()->user()?->hasPermission('cases.types'))
            <a href="{{ route('case-types.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('case-types.*') ? 'bg-orange-600 text-white' : 'hover:bg-orange-800 text-blue-100 hover:text-white' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    {{-- tags icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-5 w-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M3 10l7.586-7.586a2 2 0 012.828 0L21 9.999a2 2 0 010 2.828L13.828 20a2 2 0 01-2.828 0L3 12.999V10z" />
                    </svg>
                </div>
                <span class="truncate origin-left"
                    x-show="!compact"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-x-1"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-1">
                    {{ __('app.Case Types') }}
                </span>
            </a>
            @endif

            {{-- Letters dropdown --}}
            @if($canManageTemplates || $canViewLetters || $canComposeLetters)
            <div x-data="{ open: {{ $letterMenuOpen ? 'true' : 'false' }} }" class="space-y-1">
                <button type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-md {{ $letterMenuOpen ? 'bg-orange-600 text-white' : 'text-blue-100 hover:text-white hover:bg-orange-800' }}"
                    @click="open=!open"
                    aria-haspopup="true"
                    :aria-expanded="open.toString()">
                    <span class="flex items-center gap-3">
                        <div class="grid place-items-center w-6" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5h18M4 7l8 5 8-5M4 19h16a1 1 0 001-1V6M3 19a1 1 0 01-1-1V6" />
                            </svg>
                        </div>
                        <span class="truncate origin-left"
                            x-show="!compact"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-x-1"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-1">
                            {{ __('app.Letters') }}
                        </span>
                    </span>
                    <svg x-show="!compact" xmlns="http://www.w3.org/2000/svg" class="icon-green h-4 w-4 transition-transform duration-200"
                        :class="{ 'rotate-90': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <div x-show="open && !compact"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="pl-11 space-y-1">
                    @if($canManageTemplates)
                    <a href="{{ route('letter-templates.index') }}"
                        class="flex items-center gap-2 text-sm px-2 py-1.5 rounded-md {{ $letterTemplatesActive ? 'bg-white/10 text-white' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 4h10a2 2 0 012 2v11a2 2 0 01-2 2H8l-4 3V6a2 2 0 012-2h2z" />
                        </svg>
                        <span>{{ __('app.Letter Templates') }}</span>
                    </a>
                    @endif

                    @if($canViewLetters)
                    <a href="{{ route('letters.index') }}"
                        class="flex items-center gap-2 text-sm px-2 py-1.5 rounded-md {{ $lettersActive ? 'bg-white/10 text-white' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5h18M4 7l8 5 8-5M4 19h16a1 1 0 001-1V6M3 19a1 1 0 01-1-1V6" />
                        </svg>
                        <span>{{ __('app.Letters') }}</span>
                    </a>
                    @endif

                    @if($canComposeLetters)
                    <a href="{{ route('letters.compose') }}"
                        class="flex items-center gap-2 text-sm px-2 py-1.5 rounded-md {{ $composeActive ? 'bg-white/10 text-white' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16h8M8 12h8m-5-8h5l3 3v11a2 2 0 01-2 2H8a2 2 0 01-2-2V5a2 2 0 012-2h3z" />
                        </svg>
                        <span>{{ __('app.Compose Letter') }}</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Notifications --}}
            @if($hasNotifIndex)
            <a href="{{ route('admin.notifications.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('admin.notifications.*') ? 'bg-orange-600 text-white' : 'hover:bg-orange-800 text-blue-100 hover:text-white' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-5 w-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0h6z" />
                    </svg>
                </div>
                <span class="truncate origin-left"
                    x-show="!compact"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-x-1"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-1">
                    {{ __('app.Notifications') }}
                </span>
            </a>
            @endif

            {{-- User Control --}}
            @if($canManageUsers || $canManagePermissions || $canManageRoles)
            <div x-data="{ open: {{ $userControlOpen ? 'true' : 'false' }} }" class="space-y-1">
                <button type="button"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-md {{ $userControlOpen ? 'bg-orange-600 text-white' : 'text-blue-100 hover:text-white hover:bg-orange-800' }}"
                    @click="open=!open"
                    aria-haspopup="true"
                    :aria-expanded="open.toString()">
                    <span class="flex items-center gap-3">
                        <div class="grid place-items-center w-6" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <span class="truncate origin-left"
                            x-show="!compact"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-x-1"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-1">
                            {{ __('app.User Control') }}
                        </span>
                    </span>
                    <svg x-show="!compact" xmlns="http://www.w3.org/2000/svg" class="icon-green h-4 w-4 transition-transform duration-200"
                        :class="{ 'rotate-90': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <div x-show="open && !compact"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="pl-11 space-y-1">
                    @if($canManageUsers)
                    <a href="{{ route('users.index') }}"
                        class="flex items-center gap-2 text-sm px-2 py-1.5 rounded-md {{ $usersActive ? 'bg-white/10 text-white' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>{{ __('app.Users') }}</span>
                    </a>
                    @endif

                    @if($canManagePermissions)
                    <a href="{{ route('permissions.index') }}"
                        class="flex items-center gap-2 text-sm px-2 py-1.5 rounded-md {{ $permissionsActive ? 'bg-white/10 text-white' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M3 10l7.586-7.586a2 2 0 012.828 0L21 10a2 2 0 010 2.828L13.828 20a2 2 0 01-2.828 0L3 13v-3z" />
                        </svg>
                        <span>{{ __('app.Permissions') }}</span>
                    </a>
                    @endif

                    @if($canManageRoles)
                    <a href="{{ route('roles.index') }}"
                        class="flex items-center gap-2 text-sm px-2 py-1.5 rounded-md {{ $rolesActive ? 'bg-white/10 text-white' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ __('app.Roles') }}</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- System Settings --}}
            @if($hasSystemSettings && auth()->user()?->hasPermission('settings.manage'))
            <a href="{{ route('settings.system.edit') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('settings.system.*') ? 'bg-orange-600 text-white' : 'hover:bg-orange-800 text-blue-100 hover:text-white' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-5 w-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317L9.6 2.4m4.075 1.917l.725-1.917M4.318 10.325L2.4 9.6m1.918 4.075L2.4 14.4M19.682 10.325l1.918-.725m-1.918 4.075l1.918.725M12 8a4 4 0 100 8 4 4 0 000-8z" />
                    </svg>
                </div>
                <span class="truncate origin-left" x-show="!compact">
                    {{ __('app.System_Settings') }}
                </span>
            </a>
            @endif

            {{-- Terms & Conditions --}}
            @if($hasTerms && auth()->user()?->hasPermission('settings.manage'))
            <a href="{{ route('terms.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('terms.*') ? 'bg-orange-600 text-white' : 'hover:bg-orange-800 text-blue-100 hover:text-white' }}">
                <div class="grid place-items-center w-6" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-5 w-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6l-2-2H6a2 2 0 00-2 2v13a1 1 0 001 1h14a1 1 0 001-1V6H12z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6l2-2h4a2 2 0 012 2v13a1 1 0 01-1 1H7a1 1 0 01-1-1V6h6z" />
                    </svg>
                </div>
                <span class="truncate origin-left" x-show="!compact">
                    {{ __('app.Terms') }}
                </span>
            </a>
            @endif
        </nav>

        <footer class="p-4 border-t border-blue-800 text-xs text-blue-200">
            © {{ date('Y') }} {{ $systemSettings->app_name ?? config('app.name') }}
        </footer>
    </aside>

    {{-- Mobile overlay --}}
    <div class="md:hidden fixed inset-0 bg-black/40 z-30 transition-opacity duration-300"
        x-show="sidebar" x-cloak @click="sidebar=false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"></div>

    {{-- Main --}}
    <div class="flex-1 flex flex-col md:[transition-property:padding] md:duration-300 md:ease-in-out transition-padding">

        {{-- Topbar --}}
        <header class="relative z-50 flex items-center justify-between bg-white backdrop-blur px-3 md:px-4 py-3 border-b border-gray-200 shadow-sm">
            <div class="flex items-center gap-2">
                {{-- Mobile: open sidebar --}}
                <button type="button"
                    class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700"
                    @click="sidebar=true"
                    aria-label="{{ __('app.Open sidebar') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-5 w-5"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                {{-- Desktop: collapse / expand --}}
                <button type="button"
                    class="hidden md:inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 transition"
                    @click="toggleCompact()" :aria-pressed="compact.toString()"
                    aria-label="{{ __('app.Toggle sidebar width') }}">
                    <svg x-show="!compact" xmlns="http://www.w3.org/2000/svg" class="icon-green h-5 w-5" x-cloak
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 12H4m6 6l-6-6 6-6" />
                    </svg>
                    <svg x-show="compact" xmlns="http://www.w3.org/2000/svg" class="icon-green h-5 w-5" x-cloak
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 12h16m-6-6l6 6-6 6" />
                    </svg>
                </button>

                <h1 class="ml-1 md:ml-2 text-lg font-semibold text-gray-800">@yield('page_header', $t)</h1>
            </div>

            @php
            use Illuminate\Support\Str;
            use Illuminate\Support\Carbon;

            $uid = auth()->id();

            // stable timestamps for queries
            $now = Carbon::now();
            $cut14 = (clone $now)->subDays(14);
            $in14 = (clone $now)->addDays(14);

            // defaults
            $adminUnseenMsgs = collect();
            $adminUnseenCases = collect();
            $adminUpcomingHearings = collect();
            $adminRespondentViews = collect();

            if ($uid) {
            $adminUnseenMsgs = \DB::table('case_messages as m')
            ->join('court_cases as c', 'c.id', '=', 'm.case_id')
            ->select('m.id','m.body','m.created_at','c.case_number','c.id as case_id')
            ->whereNotNull('m.sender_applicant_id')
            ->where('m.created_at', '>=', $cut14)
            ->whereNotExists(function($q) use ($uid) {
            $q->from('admin_notification_reads as nr')
            ->whereColumn('nr.source_id', 'm.id')
            ->where('nr.type', 'message')
            ->where('nr.user_id', $uid);
            })
            ->orderByDesc('m.created_at')
            ->limit(5)
            ->get();

            $adminUnseenCases = \DB::table('court_cases as c')
            ->select('c.id','c.case_number','c.title','c.created_at')
            ->where('c.status', 'pending')
            ->whereNull('c.assigned_user_id')
            ->where('c.created_at', '>=', $cut14)
            ->whereNotExists(function($q) use ($uid) {
            $q->from('admin_notification_reads as nr')
            ->whereColumn('nr.source_id', 'c.id')
            ->where('nr.type', 'case')
            ->where('nr.user_id', $uid);
            })
            ->orderByDesc('c.created_at')
            ->limit(5)
            ->get();

            $adminUpcomingHearings = \DB::table('case_hearings as h')
            ->join('court_cases as c', 'c.id', '=', 'h.case_id')
            ->select('h.id','h.hearing_at','h.location','h.type','c.id as case_id','c.case_number')
            ->where('c.assigned_user_id', $uid)
            ->whereBetween('h.hearing_at', [$now, $in14])
            ->whereNotExists(function($q) use ($uid) {
            $q->from('admin_notification_reads as nr')
            ->whereColumn('nr.source_id', 'h.id')
            ->where('nr.type', 'hearing')
            ->where('nr.user_id', $uid);
            })
            ->orderBy('h.hearing_at')
            ->limit(5)
            ->get();
            
$adminRespondentViews = \DB::table('respondent_case_views as v')
            ->join('court_cases as c', 'c.id', '=', 'v.case_id')
            ->join('respondents as r', 'r.id', '=', 'v.respondent_id')
            ->select(
                'v.id',
                'v.viewed_at',
                'v.case_id',
                'c.case_number',
                \DB::raw("TRIM(CONCAT_WS(' ', r.first_name, r.middle_name, r.last_name)) as respondent_name")
            )
            ->where(function($q) use ($uid) {
            $q->where('c.assigned_user_id', $uid)
            ->orWhereNull('c.assigned_user_id');
            })
            ->where('v.viewed_at', '>=', $cut14)
            ->whereNotExists(function($q) use ($uid) {
            $q->from('admin_notification_reads as nr')
            ->whereColumn('nr.source_id', 'v.id')
            ->where('nr.type', 'respondent_view')
            ->where('nr.user_id', $uid);
            })
            ->orderByDesc('v.viewed_at')
            ->limit(5)
            ->get();
            }

            $__adminNotifCount = $adminUnseenMsgs->count() + $adminUnseenCases->count() + $adminUpcomingHearings->count() + ($adminRespondentViews->count() ?? 0);
            $u = auth()->user();
            @endphp

            <div class="flex items-center gap-3">
                {{-- Language switcher --}}
                @auth
                @if($hasLangSwitch)
                <div x-data="{ open:false }" class="relative">
                    <button @click="open=!open"
                        class="flex items-center gap-1 px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50 text-sm">
                        <span class="fi fi-{{ app()->getLocale() == 'am' ? 'et' : 'us' }}"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 5h16M4 10h16M10 15h10M4 20h10" />
                        </svg>
                        <span class="text-sm">{{ __('app.Language') }}</span>
                    </button>

                    <div x-cloak x-show="open" @click.outside="open=false"
                        class="absolute right-0 mt-2 w-32 rounded-md border bg-white shadow-lg z-50">
                        <div class="p-2 space-y-1">
                            <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                                class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-gray-100 rounded {{ app()->getLocale() == 'en' ? 'bg-blue-50 text-blue-700' : '' }}">
                                <span class="fi fi-us"></span>
                                {{ __('app.English') }}
                            </a>
                            <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                                class="flex items-center gap-2 w-full px-3 py-2 text-sm hover:bg-gray-100 rounded {{ app()->getLocale() == 'am' ? 'bg-blue-50 text-blue-700' : '' }}">
                                <span class="fi fi-et"></span>
                                {{ __('app.Amharic') }}
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Admin Notifications bell --}}
                @if($hasNotifIndex)
                <div class="relative" x-data="{ bell:false }">
                    <button @click="bell=!bell" type="button"
                        class="relative inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-2.5 py-1.5 hover:bg-gray-50 shadow-sm"
                        aria-label="{{ __('app.Notifications') }}" :aria-expanded="bell.toString()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0h6z" />
                        </svg>
                        @if($__adminNotifCount > 0)
                        <span class="absolute -top-1 -right-1 grid h-5 min-w-[20px] place-items-center rounded-full bg-red-600 px-1 text-[11px] font-semibold text-white">
                            {{ $__adminNotifCount > 99 ? '99+' : $__adminNotifCount }}
                        </span>
                        @endif
                    </button>

                    {{-- Dropdown --}}
                    <div x-cloak x-show="bell" @click.outside="bell=false"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                    class="absolute right-0 mt-2 w-[32rem] max-w-[90vw] rounded-md border border-gray-200 bg-white shadow-xl z-50">
                        <div class="p-3">
                            <div class="mb-2 flex items-center justify-between">
                                <div class="text-sm font-semibold text-gray-800">{{ __('app.Notifications') }}</div>
                                @if($__adminNotifCount > 0 && $hasNotifMarkAll)
                                <form method="POST" action="{{ route('admin.notifications.markAll') }}">
                                    @csrf
                                    <button class="text-xs px-2 py-1 rounded border border-gray-300 bg-white hover:bg-gray-50 text-gray-700">
                                        {{ __('app.Mark all as seen') }}
                                    </button>
                                </form>
                                @endif
                            </div>

                            @if($__adminNotifCount === 0)
                            <div class="text-sm text-gray-500">{{ __('app.youre_all_caught_up') }}</div>
                            @else
                            {{-- Applicant messages --}}
                            @if($adminUnseenMsgs->isNotEmpty())
                            <div class="mt-3">
                                <div class="text-xs font-medium text-gray-600 mb-1">{{ __('app.Applicant messages') }}</div>
                                <ul class="divide-y divide-gray-200">
                                    @foreach($adminUnseenMsgs as $m)
                                    <li class="py-2 flex items-center justify-between">
                                        <a href="{{ $hasCases ? route('cases.show', $m->case_id) : '#' }}" class="text-sm">
                                            <div class="font-medium text-gray-900">{{ $m->case_number }}</div>
                                            <div class="text-xs text-gray-600">
                                                {{ Str::limit($m->body, 80) }}
                                                · {{ \Illuminate\Support\Carbon::parse($m->created_at)->diffForHumans() }}
                                            </div>
                                        </a>
                                        @if($hasNotifMarkOne)
                                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                                            @csrf
                                            <input type="hidden" name="type" value="message">
                                            <input type="hidden" name="sourceId" value="{{ $m->id }}">
                                            <button class="text-xs px-2 py-1 rounded border border-gray-300 bg-white hover:bg-gray-50 text-gray-700">{{ __('app.Seen') }}</button>
                                        </form>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            {{-- New cases --}}
                            @if($adminUnseenCases->isNotEmpty())
                            <div class="mt-3">
                                <div class="text-xs font-medium text-gray-600 mb-1">{{ __('app.New cases') }}</div>
                                <ul class="divide-y divide-gray-200">
                                    @foreach($adminUnseenCases as $c)
                                    <li class="py-2 flex items-center justify-between">
                                        <a href="{{ $hasCases ? route('cases.show', $c->id) : '#' }}" class="text-sm">
                                            <div class="font-medium text-gray-900">{{ $c->case_number }}</div>
                                            <div class="text-xs text-gray-600">
                                                {{ Str::limit($c->title, 80) }}
                                                · {{ \Illuminate\Support\Carbon::parse($c->created_at)->diffForHumans() }}
                                            </div>
                                        </a>
                                        @if($hasNotifMarkOne)
                                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                                            @csrf
                                            <input type="hidden" name="type" value="case">
                                            <input type="hidden" name="sourceId" value="{{ $c->id }}">
                                            <button class="text-xs px-2 py-1 rounded border border-gray-300 bg-white hover:bg-gray-50 text-gray-700">{{ __('app.Seen') }}</button>
                                        </form>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            {{-- Upcoming hearings --}}
                            @if($adminUpcomingHearings->isNotEmpty())
                            <div class="mt-3">
                                <div class="text-xs font-medium text-gray-600 mb-1">{{ __('app.Upcoming hearings') }}</div>
                                <ul class="divide-y divide-gray-200">
                                    @foreach($adminUpcomingHearings as $h)
                                    <li class="py-2 flex items-center justify-between">
                                        <a href="{{ $hasCases ? route('cases.show', $h->case_id) : '#' }}" class="text-sm">
                                            <div class="font-medium text-gray-900">
                                                {{ $h->case_number }} —
                                                {{ \Illuminate\Support\Carbon::parse($h->hearing_at)->format('M d, Y H:i') }}
                                            </div>
                                            <div class="text-xs text-gray-600">
                                                {{ $h->type ?: __('app.Hearing') }} · {{ $h->location ?: '—' }}
                                            </div>
                                        </a>
                                        @if($hasNotifMarkOne)
                                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                                            @csrf
                                            <input type="hidden" name="type" value="hearing">
                                            <input type="hidden" name="sourceId" value="{{ $h->id }}">
                                            <button class="text-xs px-2 py-1 rounded border border-gray-300 bg-white hover:bg-gray-50 text-gray-700">
                                                {{ __('app.Seen') }}
                                            </button>
                                        </form>
                                        @endif
                                    </li>
                                    @endforeach

                                </ul>
                            </div>
                            @endif

                            {{-- Respondent views --}}
                            @if($adminRespondentViews->isNotEmpty())
                            <div class="mt-3">
                                <div class="text-xs font-medium text-gray-600 mb-1">Respondent views</div>
                                <ul class="divide-y divide-gray-200">
                                    @foreach($adminRespondentViews as $v)
                                    <li class="py-2 flex items-center justify-between">
                                        <a href="{{ $hasCases ? route('cases.show', $v->case_id) : '#' }}" class="text-sm">
                                            <div class="font-medium text-gray-900">{{ $v->case_number }}</div>
                                            <div class="text-xs text-gray-600">
                                                {{ $v->respondent_name ?: 'Respondent' }} viewed this case
                                                A· {{ \Illuminate\Support\Carbon::parse($v->viewed_at)->diffForHumans() }}
                                            </div>
                                        </a>
                                        @if($hasNotifMarkOne)
                                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                                            @csrf
                                            <input type="hidden" name="type" value="respondent_view">
                                            <input type="hidden" name="sourceId" value="{{ $v->id }}">
                                            <button class="text-xs px-2 py-1 rounded border border-gray-300 bg-white hover:bg-gray-50 text-gray-700">{{ __('app.Seen') }}</button>
                                        </form>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <div class="mt-3 flex items-center justify-end">
                                <a href="{{ route('admin.notifications.index') }}"
                                    class="text-xs px-2 py-1 rounded border border-gray-300 bg-white hover:bg-gray-50 text-gray-700">
                                    {{ __('app.View all') }}
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                @endauth

                {{-- Profile / Logout --}}
                @php $u = auth()->user(); @endphp
                <div x-data="{ open:false }" class="relative">
                    <button @click="open=!open" class="flex items-center gap-3 rounded-full px-3 py-1.5 hover:bg-gray-100"
                        aria-haspopup="menu" :aria-expanded="open.toString()">
                        <span class="text-sm text-gray-700 hidden sm:inline">{{ __('app.hi_name', ['name' => $u->name ?? 'Admin']) }}</span>

                        @if($u?->avatar_url)
                        <img src="{{ $u->avatar_url }}" class="w-8 h-8 rounded-full object-cover" alt="{{ __('app.Avatar') }}">
                        @else
                        <div class="w-8 h-8 rounded-full bg-blue-600 grid place-items-center font-semibold text-white" aria-hidden="true">
                            {{ strtoupper(substr($u->name ?? 'A',0,1)) }}
                        </div>
                        @endif
                    </button>

                    <div x-cloak x-show="open" @click.outside="open=false"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="absolute right-0 mt-2 w-56 rounded-md border border-gray-200 bg-white shadow-lg overflow-hidden">
                        <div class="px-4 py-2 text-xs text-gray-500 border-b border-gray-200">
                            {{ __('app.Signed in as') }} <span class="text-gray-800">{{ $u?->email }}</span>
                        </div>

                        @if($hasProfileEdit)
                        <a href="{{ route('profile.edit') }}"
                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ __('app.Profile') }}
                        </a>
                        @endif

                        @if($hasLogout)
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon-green h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                {{ __('app.Logout') }}
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 p-4 md:p-6 bg-gray-50">
            @if(session('success'))
            <div class="mb-4 rounded-md bg-green-100 border border-green-300 text-green-800 px-3 py-2">
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mb-4 rounded-md bg-red-100 border border-red-300 text-red-800 px-3 py-2">
                {{ session('error') }}
            </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    {{-- Alpine helpers --}}
    <script>
        function layoutState() {
            return {
                sidebar: false, // mobile only
                compact: false, // desktop collapse state (persisted)
                init() {
                    try {
                        const saved = localStorage.getItem('admin_sidebar_compact');
                        this.compact = saved === '1';
                    } catch (e) {}
                    this.$watch('compact', (val) => {
                        try {
                            localStorage.setItem('admin_sidebar_compact', val ? '1' : '0');
                        } catch (e) {}
                    });
                },
                toggleCompact() {
                    this.compact = !this.compact;
                }
            }
        }
    </script>
    @stack('scripts')
</body>

</html>
