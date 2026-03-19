<div x-data="{ open: false }" class="relative">
    <button
        type="button"
        @click="open = !open"
        class="inline-flex items-center gap-2 rounded-lg border border-[var(--border-strong)] bg-[var(--surface-strong)] px-3 py-2 text-sm font-medium text-[var(--text-muted)] shadow-sm transition-fast hover:scale-[var(--scale-hover)] hover:bg-[var(--surface-soft)] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:scale-[var(--scale-press)]"
        aria-haspopup="menu"
        :aria-expanded="open.toString()"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v2.25M12 18.75V21M4.97 4.97l1.59 1.59M17.44 17.44l1.59 1.59M3 12h2.25M18.75 12H21M4.97 19.03l1.59-1.59M17.44 6.56l1.59-1.59M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
        </svg>
        <span>{{ __('app.Theme') }}</span>
    </button>

    <div
        x-cloak
        x-show="open"
        @click.outside="open = false"
        x-transition:enter="motion-enter-fast"
        x-transition:enter-start="motion-slide-up-start"
        x-transition:enter-end="motion-slide-up-end"
        x-transition:leave="motion-leave"
        x-transition:leave-start="motion-slide-up-end"
        x-transition:leave-end="motion-slide-up-start"
        class="absolute right-0 z-50 mt-2 w-40 rounded-xl border border-[var(--border)] bg-[var(--surface-strong)] p-1.5 shadow-xl"
        role="menu"
    >
        <button type="button" @click="set('light'); open = false" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm text-[var(--text-muted)] hover:bg-[var(--surface-soft)]" :class="{ 'bg-[var(--surface-soft)] text-[var(--text)]': isActive('light') }">Light</button>
        <button type="button" @click="set('dark'); open = false" class="mt-1 flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm text-[var(--text-muted)] hover:bg-[var(--surface-soft)]" :class="{ 'bg-[var(--surface-soft)] text-[var(--text)]': isActive('dark') }">Dark</button>
        <button type="button" @click="set('system'); open = false" class="mt-1 flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm text-[var(--text-muted)] hover:bg-[var(--surface-soft)]" :class="{ 'bg-[var(--surface-soft)] text-[var(--text)]': isActive('system') }">System</button>
    </div>
</div>
