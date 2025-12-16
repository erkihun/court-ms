<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    @php
    $uid = auth()->id();
    $now = now();

    // 1) Unseen applicant messages (last 14 days)
    $adminUnseenMsgs = \DB::table('case_messages as m')
    ->join('court_cases as c', 'c.id', '=', 'm.case_id')
    ->select('m.id','m.body','m.created_at','c.case_number','c.id as case_id')
    ->whereNotNull('m.sender_applicant_id')
    ->where('m.created_at', '>=', $now->subDays(14))
    ->whereNotExists(function($q) use ($uid) {
    $q->from('admin_notification_reads as nr')
    ->whereColumn('nr.source_id', 'm.id')
    ->where('nr.type', 'message')
    ->where('nr.user_id', $uid);
    })
    ->orderByDesc('m.created_at')
    ->limit(5)
    ->get();

    // 2) New/unseen cases (pending & unassigned) in last 14 days
    $adminUnseenCases = \DB::table('court_cases as c')
    ->select('c.id','c.case_number','c.title','c.created_at')
    ->where('c.status', 'pending')
    ->whereNull('c.assigned_user_id')
    ->where('c.created_at', '>=', $now->copy()->subDays(14))
    ->whereNotExists(function($q) use ($uid) {
    $q->from('admin_notification_reads as nr')
    ->whereColumn('nr.source_id', 'c.id')
    ->where('nr.type', 'case')
    ->where('nr.user_id', $uid);
    })
    ->orderByDesc('c.created_at')
    ->limit(5)
    ->get();

    // 3) Upcoming hearings for cases assigned to me (next 14 days)
    $adminUpcomingHearings = \DB::table('case_hearings as h')
    ->join('court_cases as c', 'c.id', '=', 'h.case_id')
    ->select('h.id','h.hearing_at','c.id as case_id','c.case_number')
    ->where('c.assigned_user_id', $uid)
    ->whereBetween('h.hearing_at', [$now, $now->copy()->addDays(14)])
    ->whereNotExists(function($q) use ($uid) {
    $q->from('admin_notification_reads as nr')
    ->whereColumn('nr.source_id', 'h.id')
    ->where('nr.type', 'hearing')
    ->where('nr.user_id', $uid);
    })
    ->orderBy('h.hearing_at')
    ->limit(5)
    ->get();

    $__adminNotifCount = $adminUnseenMsgs->count() + $adminUnseenCases->count() + $adminUpcomingHearings->count();
    @endphp

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    {{-- Add any other admin links here --}}
                    <x-nav-link :href="route('cases.index')" :active="request()->routeIs('cases.*')">
                        {{ __('Cases') }}
                    </x-nav-link>
                    @if(function_exists('userHasPermission') ? userHasPermission('decision.view') : (auth()->user()?->hasPermission('decision.view') ?? false))
                    <x-nav-link :href="route('decisions.index')" :active="request()->routeIs('decisions.*')">
                        {{ __('decisions.index.title') }}
                    </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-4">
                {{-- Admin Notifications Bell --}}
                <div class="relative" x-data="{ bell:false }">
                    <button @click="bell = !bell"
                        class="relative inline-flex items-center justify-center rounded-md border px-2.5 py-1.5 hover:bg-gray-50">
                        {{-- bell icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0h6z" />
                        </svg>
                        @if($__adminNotifCount > 0)
                        <span class="absolute -top-1 -right-1 grid h-5 min-w-[20px] place-items-center rounded-full bg-red-600 px-1 text-[11px] font-semibold text-white">
                            {{ $__adminNotifCount > 9 ? '9+' : $__adminNotifCount }}
                        </span>
                        @endif
                    </button>

                    {{-- Dropdown --}}
                    <div x-cloak x-show="bell" @click.outside="bell=false"
                        class="absolute right-0 mt-2 w-[32rem] max-w-[90vw] rounded-md border bg-white shadow-lg">
                        <div class="p-3">
                            <div class="mb-2 flex items-center justify-between">
                                <div class="text-sm font-semibold text-slate-700">Notifications</div>
                                @if($__adminNotifCount > 0)
                                <form method="POST" action="{{ route('admin.notifications.markAll') }}">
                                    @csrf
                                    <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">Mark all as seen</button>
                                </form>
                                @endif
                            </div>

                            @if($__adminNotifCount === 0)
                            <div class="text-sm text-slate-500">You’re all caught up.</div>
                            @else
                            {{-- Messages from applicants --}}
                            @if($adminUnseenMsgs->isNotEmpty())
                            <div class="mt-3">
                                <div class="text-xs font-medium text-slate-500 mb-1">Applicant messages</div>
                                <ul class="divide-y">
                                    @foreach($adminUnseenMsgs as $m)
                                    <li class="py-2 flex items-center justify-between">
                                        <a href="{{ route('cases.show', $m->case_id) }}" class="text-sm">
                                            <div class="font-medium text-slate-800">{{ $m->case_number }}</div>
                                            <div class="text-xs text-slate-500">
                                                {{ \Illuminate\Support\Str::limit($m->body, 80) }}
                                                · {{ \Illuminate\Support\Carbon::parse($m->created_at)->diffForHumans() }}
                                            </div>
                                        </a>
                                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                                            @csrf
                                            <input type="hidden" name="type" value="message">
                                            <input type="hidden" name="sourceId" value="{{ $m->id }}">
                                            <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">Seen</button>
                                        </form>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            {{-- New pending & unassigned cases --}}
                            @if($adminUnseenCases->isNotEmpty())
                            <div class="mt-3">
                                <div class="text-xs font-medium text-slate-500 mb-1">New cases</div>
                                <ul class="divide-y">
                                    @foreach($adminUnseenCases as $c)
                                    <li class="py-2 flex items-center justify-between">
                                        <a href="{{ route('cases.show', $c->id) }}" class="text-sm">
                                            <div class="font-medium text-slate-800">{{ $c->case_number }}</div>
                                            <div class="text-xs text-slate-500">
                                                {{ \Illuminate\Support\Str::limit($c->title, 80) }}
                                                · {{ \Illuminate\Support\Carbon::parse($c->created_at)->diffForHumans() }}
                                            </div>
                                        </a>
                                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                                            @csrf
                                            <input type="hidden" name="type" value="case">
                                            <input type="hidden" name="sourceId" value="{{ $c->id }}">
                                            <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">Seen</button>
                                        </form>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            {{-- Upcoming hearings for my assigned cases --}}
                            @if($adminUpcomingHearings->isNotEmpty())
                            <div class="mt-3">
                                <div class="text-xs font-medium text-slate-500 mb-1">Upcoming hearings</div>
                                <ul class="divide-y">
                                    @foreach($adminUpcomingHearings as $h)
                                    <li class="py-2 flex items-center justify-between">
                                        <a href="{{ route('cases.show', $h->case_id) }}" class="text-sm">
                                            <div class="font-medium text-slate-800">
                                                {{ $h->case_number }} — {{ \App\Support\EthiopianDate::format($h->hearing_at, withTime: true) }}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                {{ $h->type ?: 'Hearing' }} · {{ $h->location ?: '—' }}
                                            </div>
                                        </a>
                                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                                            @csrf
                                            <input type="hidden" name="type" value="hearing">
                                            <input type="hidden" name="sourceId" value="{{ $h->id }}">
                                            <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">Seen</button>
                                        </form>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <div class="mt-3 flex items-center justify-end">
                                <a href="{{ route('admin.notifications.index') }}"
                                    class="text-xs px-2 py-1 rounded border hover:bg-slate-50">View all</a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('cases.index')" :active="request()->routeIs('cases.*')">
                {{ __('Cases') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.notifications.index')" :active="request()->routeIs('admin.notifications.*')">
                {{ __('Notifications') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
