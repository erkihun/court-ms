{{--
  Compact theme toggle: cycles light → dark → system on each click.
  Relies on themeSystem() registered in app.js (set / isActive / mode).
--}}
<button
    type="button"
    @click="set(mode === 'light' ? 'dark' : mode === 'dark' ? 'system' : 'light')"
    class="topnav-icon-btn"
    :title="mode === 'light' ? 'Switch to dark mode' : mode === 'dark' ? 'Switch to system mode' : 'Switch to light mode'"
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
