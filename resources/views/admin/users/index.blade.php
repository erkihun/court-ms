{{-- resources/views/users/index.blade.php --}}
<x-admin-layout title="Users">
    @section('page_header','Users')

    {{-- Filters / search --}}
    <form method="GET" class="mb-4 flex flex-col md:flex-row gap-3 md:items-center">
        <input name="q" value="{{ request('q','') }}" placeholder="Search by name or email…"
            class="w-full md:w-80 px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <select name="status" class="px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status')==='active' )>Active</option>
            <option value="inactive" @selected(request('status')==='inactive' )>Inactive</option>
        </select>
        <button class="px-3 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">Filter</button>
        @if(request()->hasAny(['q','status']) && (request('q')!=='' || request('status')!==''))
        <a href="{{ route('users.index') }}" class="px-3 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">Reset</a>
        @endif
        <a href="{{ route('users.create') }}" class="md:ml-auto px-3 py-2 rounded bg-green-600 hover:bg-green-700 text-white">New User</a>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-700 border-b border-gray-200">
                <tr>
                    <th class="p-3 text-left font-medium">User</th>
                    <th class="p-3 text-left font-medium">Email</th>
                    <th class="p-3 text-left font-medium">Roles</th>
                    <th class="p-3 text-left font-medium">Status</th>
                    <th class="p-3 text-left font-medium">Verified</th>
                    <th class="p-3 text-left font-medium">Created</th>
                    <th class="p-3 text-left font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($users as $u)
                <tr class="hover:bg-gray-50">
                    <td class="p-3">
                        <div class="flex items-center gap-3">
                            @if($u->avatar_url)
                            <img src="{{ $u->avatar_url }}" class="w-8 h-8 rounded-full object-cover border border-gray-200" alt="Avatar">
                            @else
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 grid place-items-center text-xs font-semibold border border-blue-200">
                                {{ strtoupper(substr($u->name ?? 'U',0,1)) }}
                            </div>
                            @endif
                            <div>
                                <div class="font-medium text-gray-900">{{ $u->name }}</div>
                                @if($u->signature_path)
                                <div class="text-[10px] mt-0.5 inline-block px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 border border-emerald-200">has signature</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="p-3 text-gray-600">{{ $u->email }}</td>
                    <td class="p-3 text-gray-600">
                        @php $roleNames = $u->roles?->pluck('name')->all() ?? []; @endphp
                        {{ empty($roleNames) ? '—' : implode(', ', $roleNames) }}
                    </td>
                    <td class="p-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ $u->status==='active'
                                ? 'bg-green-100 text-green-700 border border-green-200'
                                : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                            {{ ucfirst($u->status) }}
                        </span>
                    </td>
                    <td class="p-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ $u->hasVerifiedEmail()
                                ? 'bg-emerald-100 text-emerald-700 border border-emerald-200'
                                : 'bg-yellow-100 text-yellow-700 border border-yellow-200' }}">
                            {{ $u->hasVerifiedEmail() ? 'Verified' : 'Unverified' }}
                        </span>
                    </td>
                    <td class="p-3 text-gray-600">{{ \App\Support\EthiopianDate::format($u->created_at) }}</td>
                    <td class="p-3">
                        <div class="flex gap-2">
                            <a href="{{ route('users.show',$u) }}"
                                class="px-2 py-1 rounded bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs">View</a>

                            <a href="{{ route('users.edit',$u) }}"
                                class="px-2 py-1 rounded bg-blue-600 hover:bg-blue-700 text-white text-xs">Edit</a>

                            <form method="POST" action="{{ route('users.destroy',$u) }}"
                                onsubmit="return confirm('Delete this user?')">
                                @csrf @method('DELETE')
                                <button class="px-2 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-xs">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-6 text-center text-gray-500">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</x-admin-layout>
