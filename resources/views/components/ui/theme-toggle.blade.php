{{--
  Theme picker panel: mode (light/dark/system) + accent color swatches.
  Relies on themeSystem() registered in app.js (parent scope on <html>).
--}}
<div class="relative" x-data="{ panelOpen: false }" @keydown.escape.window="panelOpen = false">

    {{-- Trigger button --}}
    <button
        type="button"
        @click="panelOpen = !panelOpen"
        class="topnav-icon-btn"
        :title="panelOpen ? 'Close theme picker' : 'Open theme picker'"
        aria-label="{{ __('app.Theme') }}"
    >
        {{-- Sun (light) --}}
        <svg x-show="mode === 'light'" xmlns="http://www.w3.org/2000/svg" class="h-[1.05rem] w-[1.05rem]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M12 3v2.25M12 18.75V21M4.97 4.97l1.59 1.59M17.44 17.44l1.59 1.59M3 12h2.25M18.75 12H21M4.97 19.03l1.59-1.59M17.44 6.56l1.59-1.59M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
        </svg>
        {{-- Moon (dark) --}}
        <svg x-show="mode === 'dark'" xmlns="http://www.w3.org/2000/svg" class="h-[1.05rem] w-[1.05rem]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
        </svg>
        {{-- Monitor (system) --}}
        <svg x-show="mode === 'system'" xmlns="http://www.w3.org/2000/svg" class="h-[1.05rem] w-[1.05rem]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M9 17H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-4M12 17v4m-4 0h8" />
        </svg>
    </button>

    {{-- Panel --}}
    <div
        x-show="panelOpen"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        @click.outside="panelOpen = false"
        class="absolute right-0 top-full z-50 mt-2 w-52 rounded-xl border border-[var(--border)] bg-[var(--surface-strong)] p-3 shadow-xl"
        style="display:none"
    >
        {{-- Mode row --}}
        <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-[var(--text-subtle)]">Mode</p>
        <div class="flex gap-1.5 mb-4">
            {{-- Light --}}
            <button type="button"
                @click="set('light')"
                :class="isActive('light') ? 'bg-[var(--border-strong)] text-[var(--text)] ring-2 ring-[rgb(var(--ac))]' : 'text-[var(--text-muted)] hover:bg-[var(--border)]'"
                class="flex flex-1 flex-col items-center gap-1 rounded-lg py-2 text-[11px] font-medium transition-all duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 3v2.25M12 18.75V21M4.97 4.97l1.59 1.59M17.44 17.44l1.59 1.59M3 12h2.25M18.75 12H21M4.97 19.03l1.59-1.59M17.44 6.56l1.59-1.59M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                </svg>
                Light
            </button>
            {{-- Dark --}}
            <button type="button"
                @click="set('dark')"
                :class="isActive('dark') ? 'bg-[var(--border-strong)] text-[var(--text)] ring-2 ring-[rgb(var(--ac))]' : 'text-[var(--text-muted)] hover:bg-[var(--border)]'"
                class="flex flex-1 flex-col items-center gap-1 rounded-lg py-2 text-[11px] font-medium transition-all duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                </svg>
                Dark
            </button>
            {{-- System --}}
            <button type="button"
                @click="set('system')"
                :class="isActive('system') ? 'bg-[var(--border-strong)] text-[var(--text)] ring-2 ring-[rgb(var(--ac))]' : 'text-[var(--text-muted)] hover:bg-[var(--border)]'"
                class="flex flex-1 flex-col items-center gap-1 rounded-lg py-2 text-[11px] font-medium transition-all duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 17H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-4M12 17v4m-4 0h8" />
                </svg>
                Auto
            </button>
        </div>

        {{-- Accent row --}}
        <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-[var(--text-subtle)]">Accent</p>
        <div class="flex items-center gap-2">
            {{-- Blue --}}
            <button type="button"
                @click="setAccent('blue')"
                :class="isAccent('blue') ? 'ring-2 ring-offset-2 ring-blue-500' : 'opacity-70 hover:opacity-100'"
                class="h-6 w-6 rounded-full bg-blue-600 transition-all duration-150"
                title="Blue"></button>
            {{-- Teal --}}
            <button type="button"
                @click="setAccent('teal')"
                :class="isAccent('teal') ? 'ring-2 ring-offset-2 ring-teal-500' : 'opacity-70 hover:opacity-100'"
                class="h-6 w-6 rounded-full bg-teal-600 transition-all duration-150"
                title="Teal"></button>
            {{-- Violet --}}
            <button type="button"
                @click="setAccent('violet')"
                :class="isAccent('violet') ? 'ring-2 ring-offset-2 ring-violet-500' : 'opacity-70 hover:opacity-100'"
                class="h-6 w-6 rounded-full bg-violet-600 transition-all duration-150"
                title="Violet"></button>
            {{-- Emerald --}}
            <button type="button"
                @click="setAccent('emerald')"
                :class="isAccent('emerald') ? 'ring-2 ring-offset-2 ring-emerald-500' : 'opacity-70 hover:opacity-100'"
                class="h-6 w-6 rounded-full bg-emerald-600 transition-all duration-150"
                title="Emerald"></button>
            {{-- Rose --}}
            <button type="button"
                @click="setAccent('rose')"
                :class="isAccent('rose') ? 'ring-2 ring-offset-2 ring-rose-500' : 'opacity-70 hover:opacity-100'"
                class="h-6 w-6 rounded-full bg-rose-600 transition-all duration-150"
                title="Rose"></button>
        </div>
    </div>

</div>
