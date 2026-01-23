<x-admin-layout title="{{ __('chat.title') }}">
    @section('page_header', __('chat.title'))

    <div class="mt-6 grid gap-6 lg:grid-cols-[380px_1fr]">

        {{-- Contacts panel --}}
        <aside class="rounded-2xl border border-gray-200 bg-white shadow-lg p-4 space-y-4">
            {{-- Header --}}
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ __('chat.title') }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ __('chat.select_conversation') }}</p>
                </div>
                <div class="relative">
                    <button class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Search --}}
            <div class="relative">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text"
                    placeholder="{{ __('chat.search_placeholder') }}"
                    class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
            </div>

            {{-- Online status filter --}}
            <div class="flex items-center space-x-2 overflow-x-auto pb-2">
                <button class="px-3 py-1.5 text-sm font-medium rounded-full bg-blue-600 text-white">
                    {{ __('chat.all') }}
                </button>
                <button class="px-3 py-1.5 text-sm font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200">
                    {{ __('chat.online') }}
                </button>
                <button class="px-3 py-1.5 text-sm font-medium rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200">
                    {{ __('chat.unread') }}
                </button>
            </div>

            {{-- Contact list --}}
            <div class="space-y-2 max-h-[calc(100vh-240px)] overflow-y-auto">
                @foreach($users as $user)
                @php
                    $handle = '@' . \Illuminate\Support\Str::slug($user->name ?? 'team');
                    $initials = strtoupper(substr($user->name ?? 'US', 0, 2));
                    $isSelected = $selectedUser && $user->id === $selectedUser->id;
                    $avatar = $user->avatar_url ?? (!empty($user->avatar_path) ? asset('storage/' . $user->avatar_path) : null);
                    $isOnline = true; // Assume online for demo
                    $unreadCount = rand(0, 5); // Demo data
                @endphp
                <div
                    role="button"
                    tabindex="0"
                    data-chat-user-id="{{ $user->id }}"
                    data-chat-user-name="{{ $user->name ?? __('chat.no_name') }}"
                    data-chat-user-handle="{{ $handle }}"
                    data-chat-user-initials="{{ $initials }}"
                    class="group flex items-center gap-3 p-3 rounded-xl transition-all duration-200 cursor-pointer {{ $isSelected ? 'bg-blue-50 border border-blue-100 shadow-sm' : 'hover:bg-gray-50' }}">
                    <div class="relative">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full {{ $isSelected ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }} font-medium overflow-hidden">
                            @if($avatar)
                            <img src="{{ $avatar }}" alt="{{ $user->name ?? __('chat.no_name') }}" class="h-full w-full object-cover">
                            @else
                            {{ $initials }}
                            @endif
                        </div>
                        @if($isOnline)
                        <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-green-500"></span>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-900 truncate">
                                {{ $user->name ?? __('chat.no_name') }}
                            </h4>
                            <span class="text-xs text-gray-400">
                                {{ $user->created_at ? \Illuminate\Support\Carbon::parse($user->created_at)->format('h:i A') : '1m ago' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between mt-0.5">
                            <p class="text-xs text-gray-500 truncate">
                                {{ $unreadCount > 0 ? 'New message' : $handle }}
                            </p>
                            @if($unreadCount > 0)
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-xs font-medium text-white">
                                {{ $unreadCount }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </aside>

        @php
            $headerAvatar = $selectedUser?->avatar_url ?? (!empty($selectedUser?->avatar_path) ? asset('storage/' . $selectedUser->avatar_path) : null);
        @endphp

        {{-- Chat pane --}}
        <section class="rounded-2xl border border-gray-200 bg-white shadow-lg flex flex-col overflow-hidden">
            {{-- Chat header --}}
            <header class="border-b border-gray-100 bg-white px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <span data-chat-header-initials
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-white font-semibold text-sm overflow-hidden">
                                @if($headerAvatar)
                                <img src="{{ $headerAvatar }}" alt="{{ $selectedUser?->name }}" class="h-full w-full object-cover">
                                @else
                                {{ strtoupper(substr($selectedUser?->name ?? 'US', 0, 2)) }}
                                @endif
                            </span>
                            <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-green-500 border-2 border-white"></span>
                        </div>
                        <div>
                            <h1 data-chat-header-name class="text-base font-semibold text-gray-900">{{ $selectedUser?->name ?? __('chat.select_user') }}</h1>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                <p data-chat-header-handle class="text-xs text-gray-500">{{ __('chat.online') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                        <button class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </button>
                        <button class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </header>

            {{-- Messages area --}}
            <div id="admin-chat-messages" class="flex-1 space-y-4 overflow-y-auto p-6 bg-gradient-to-b from-gray-50/50 to-white">
                <div class="text-center mb-6">
                    <span class="inline-block px-3 py-1 text-xs font-medium text-gray-500 bg-white border border-gray-200 rounded-full">
                        {{ __('chat.conversation_started') }}
                    </span>
                </div>

                @foreach($messages as $message)
                @php
                $isCurrentUser = auth()->id() === $message->sender_user_id;
                $isSystem = !$message->sender_user_id;
                @endphp

                @if($isSystem)
                {{-- System message --}}
                <div class="flex justify-center">
                    <div class="px-3 py-1.5 text-xs font-medium text-gray-500 bg-gray-100 rounded-full">
                        {{ $message->message }}
                    </div>
                </div>
                @else
                {{-- User message --}}
                <div class="flex {{ $isCurrentUser ? 'justify-end' : 'justify-start' }} group">
                    <div class="max-w-[75%] relative">
                        @if(!$isCurrentUser)
                        <div class="flex items-end gap-2 mb-1">
                            <span class="text-xs font-medium text-gray-700">{{ $message->sender?->name }}</span>
                            <span class="text-xs text-gray-400">{{ $message->created_at->format('h:i A') }}</span>
                        </div>
                        @endif

                        <div class="{{ $isCurrentUser ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white' : 'bg-white border border-gray-200' }} rounded-2xl p-4 shadow-sm">
                            <p class="text-sm leading-relaxed">{{ $message->message }}</p>
                        </div>

                        @if($isCurrentUser)
                        <div class="flex items-center justify-end gap-1.5 mt-1">
                            <span class="text-xs text-gray-400">{{ $message->created_at->format('h:i A') }}</span>
                            @if($message->read_at)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                @endif
                @endforeach
            </div>

            {{-- Message input --}}
            <form id="admin-chat-form" method="POST" action="{{ route('admin.chat.messages') }}"
                class="border-t border-gray-100 bg-white p-4">
                @csrf
                <input type="hidden" name="recipient_user_id" value="{{ $selectedUser?->id }}">

                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <div class="relative">
                            <textarea name="message" rows="1"
                                class="w-full px-4 py-3 text-sm text-gray-800 placeholder-gray-400 bg-gray-50 border border-gray-200 rounded-xl resize-none focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
                                placeholder="{{ __('chat.write_message') }}"
                                oninput="this.style.height = 'auto'; this.style.height = (this.scrollHeight) + 'px';"></textarea>

                            <div class="absolute right-3 bottom-3 flex items-center gap-2">
                                <button type="button" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </button>
                                <button type="button" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg transition-transform hover:scale-105 active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </div>
            </form>
        </section>
    </div>

    @php
    $serializedMessages = $messages->map(function ($message) {
    return [
    'id' => $message->id,
    'message' => $message->message,
    'sender_name' => $message->sender?->name,
    'sender_id' => $message->sender_user_id,
    'recipient_id' => $message->recipient_user_id,
    'created_at' => $message->created_at->toIsoString(),
    'read_at' => $message->read_at?->toIsoString(),
    ];
    })->toArray();
    @endphp

    @push('scripts')
    <script>
        window.ADMIN_CHAT_MESSAGES = @json($serializedMessages);
        window.ADMIN_CHAT_CURRENT_USER_ID = @json(auth()->id());
        window.ADMIN_CHAT_SYSTEM_LABEL = @json(__('chat.system'));
        window.ADMIN_CHAT_CONVERSATION_URL_TEMPLATE = @json(route('admin.chat.conversation', ['user' => '__USER__']));
        window.ADMIN_CHAT_INITIAL_RECIPIENT_ID = @json($selectedUser?->id);
    </script>
    @endpush
</x-admin-layout>
