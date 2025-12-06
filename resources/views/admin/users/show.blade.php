<x-admin-layout title="User Profile">
    @section('page_header','User Profile')

    @php
    $nameParts = preg_split('/\s+/', trim($user->name ?? ''), 3) ?: [];
    $firstName = $nameParts[0] ?? '';
    if (count($nameParts) >= 3) {
        $middleName = $nameParts[1] ?? '';
        $lastName = $nameParts[2] ?? '';
    } elseif (count($nameParts) === 2) {
        $middleName = '';
        $lastName = $nameParts[1] ?? '';
    } else {
        $middleName = '';
        $lastName = '';
    }
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column --}}
        <div class="space-y-6">
            {{-- Profile Card --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6 text-center">
                    <div class="relative inline-block">
                        <div class="w-28 h-28 rounded-full overflow-hidden mx-auto bg-gray-100 border-4 border-white shadow">
                            @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" class="w-full h-full object-cover" alt="Avatar">
                            @else
                            <div class="w-full h-full flex items-center justify-center bg-blue-50 text-blue-600 text-4xl font-bold">
                                {{ strtoupper(substr($user->name ?? 'U',0,1)) }}
                            </div>
                            @endif
                        </div>
                        <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $user->status === 'active'
                                    ? 'bg-green-100 text-green-700 border border-green-200'
                                    : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                        <p class="text-gray-600 mt-1">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="border-t border-gray-100 p-4">
                    <div class="flex space-x-2">
                        <a href="{{ route('users.edit',$user) }}"
                            class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 text-center">
                            Edit Profile
                        </a>
                        <a href="{{ route('users.index') }}"
                            class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors duration-200">
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-3 border-b">User Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-5">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">First Name</label>
                                <div class="text-gray-900 font-medium">{{ $firstName }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Middle Name</label>
                                <div class="text-gray-900 font-medium">{{ $middleName ?: '—' }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Last Name</label>
                                <div class="text-gray-900 font-medium">{{ $lastName }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Full Name</label>
                                <div class="text-gray-900 font-medium">{{ $user->name }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Email Address</label>
                                <div class="text-gray-900 font-medium">{{ $user->email }}</div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Signature</label>
                                    @if($user->signature_url)
                                    <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                        <img src="{{ $user->signature_url }}"
                                            class="max-h-20 mx-auto object-contain"
                                            alt="Signature">
                                    </div>
                                    <a href="{{ $user->signature_url }}" target="_blank"
                                        class="inline-block mt-2 text-sm text-blue-600 hover:text-blue-800 hover:underline">
                                        View full size
                                    </a>
                                    @else
                                    <div class="text-gray-400 text-sm italic py-3">No signature uploaded</div>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Stamp</label>
                                    @if($user->stamp_url)
                                    <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                        <img src="{{ $user->stamp_url }}"
                                            class="max-h-20 mx-auto object-contain"
                                            alt="Stamp">
                                    </div>
                                    <a href="{{ $user->stamp_url }}" target="_blank"
                                        class="inline-block mt-2 text-sm text-blue-600 hover:text-blue-800 hover:underline">
                                        View full size
                                    </a>
                                    @else
                                    <div class="text-gray-400 text-sm italic py-3">No stamp uploaded</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Created At</label>
                                <div class="text-gray-700">{{ optional($user->created_at)->format('M d, Y h:i A') }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Updated At</label>
                                <div class="text-gray-700">{{ optional($user->updated_at)->format('M d, Y h:i A') }}</div>
                            </div>
                        </div>

                        <div class="md:col-span-2 pt-4 border-top border-gray-100">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Assigned Roles</label>
                            <div class="flex flex-wrap gap-2">
                                @php
                                $roles = $user->roles?->pluck('name')->all() ?? [];
                                @endphp

                                @if(!empty($roles))
                                @foreach($roles as $role)
                                <span class="px-3 py-1 bg-blue-50 text-blue-700 text-sm font-medium rounded-full border border-blue-100">
                                    {{ $role }}
                                </span>
                                @endforeach
                                @else
                                <span class="text-gray-400 text-sm">No roles assigned</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
