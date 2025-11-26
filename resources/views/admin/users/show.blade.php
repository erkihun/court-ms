<x-admin-layout title="User Profile">
    @section('page_header','User Profile')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Avatar & Signature --}}
        <div class="space-y-6">
            <div class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm text-center">
                <div class="mx-auto w-28 h-28 rounded-full overflow-hidden bg-gray-100 border border-gray-300">
                    @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" class="w-full h-full object-cover" alt="Avatar">
                    @else
                    <div class="w-full h-full grid place-items-center text-3xl font-semibold bg-blue-100 text-blue-700">
                        {{ strtoupper(substr($user->name ?? 'U',0,1)) }}
                    </div>
                    @endif
                </div>
                <div class="mt-3 font-medium text-lg text-gray-900">{{ $user->name }}</div>
                <div class="text-sm text-gray-600">{{ $user->email }}</div>

                <div class="mt-3">
                    <span class="px-2 py-0.5 rounded text-xs font-medium
                        {{ $user->status==='active'
                            ? 'bg-green-100 text-green-700 border border-green-200'
                            : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                        {{ ucfirst($user->status) }}
                    </span>
                </div>

                <div class="mt-4 flex justify-center gap-2">
                    <a href="{{ route('users.edit',$user) }}"
                        class="px-3 py-1.5 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm">Edit</a>
                    <a href="{{ route('users.index') }}"
                        class="px-3 py-1.5 rounded bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm">Back</a>
                </div>
            </div>

            <div class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm">
                <h3 class="text-sm text-gray-700 mb-3 font-medium">Signature</h3>
                @if($user->signature_url)
                <img src="{{ $user->signature_url }}" class="max-h-24 border border-gray-200" alt="Signature">
                <div class="mt-2">
                    <a href="{{ $user->signature_url }}" target="_blank"
                        class="text-xs text-blue-600 underline hover:text-blue-700">Open image</a>
                </div>
                @else
                <div class="text-gray-500 text-sm">No signature uploaded.</div>
                @endif
            </div>
        </div>

        {{-- Right: Details --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm">
                <h3 class="text-sm text-gray-700 mb-3 font-medium">Details</h3>
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-gray-500">Name</div>
                        <div class="text-gray-900 font-medium">{{ $user->name }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">Email</div>
                        <div class="text-gray-900 font-medium">{{ $user->email }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">Created</div>
                        <div class="text-gray-900">{{ optional($user->created_at)->format('M d, Y h:i A') }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">Updated</div>
                        <div class="text-gray-900">{{ optional($user->updated_at)->format('M d, Y h:i A') }}</div>
                    </div>
                    <div class="md:col-span-2">
                        <div class="text-gray-500">Roles</div>
                        <div class="text-gray-900">
                            @php $roles = $user->roles?->pluck('name')->all() ?? []; @endphp
                            {{ empty($roles) ? '—' : implode(', ', $roles) }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- (Optional) Print-friendly card --}}
            <div class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm print:bg-white print:text-black">
                <h3 class="text-sm text-gray-700 mb-3 font-medium">Printable Card</h3>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-100 border border-gray-300">
                        @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" class="w-full h-full object-cover" alt="Avatar">
                        @endif
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                        <div class="text-gray-600 text-sm">{{ $user->email }}</div>
                        <div class="text-gray-500 text-xs">
                            Roles: {{ empty($roles) ? '—' : implode(', ', $roles) }}
                        </div>
                    </div>
                </div>
                @if($user->signature_url)
                <div class="mt-4">
                    <div class="text-gray-500 text-xs mb-1">Signature</div>
                    <img src="{{ $user->signature_url }}" class="h-12 border border-gray-200" alt="Signature">
                </div>
                @endif
                <div class="mt-4">
                    <button onclick="window.print()" class="px-3 py-1.5 rounded bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm">
                        Print
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>