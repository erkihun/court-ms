{{-- resources/views/admin/applicants/index.blade.php --}}
<x-admin-layout title="Applicants">
    @section('page_header', 'Applicants')

    <form method="GET" class="mb-4 flex flex-col gap-3 md:flex-row md:items-center">
        <input name="q" value="{{ $q ?? '' }}" placeholder="Search by name, email, or phone..."
            class="w-full md:w-72 px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <select name="status"
            class="w-full md:w-40 px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All statuses</option>
            <option value="active" @selected(($status ?? '') === 'active')>{{ __('app.Active') }}</option>
            <option value="inactive" @selected(($status ?? '') === 'inactive')>{{ __('app.Inactive') }}</option>
        </select>

        <div class="flex items-center gap-2">
            <button type="submit" class="px-3 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">Filter</button>
            @if(($q ?? '') !== '' || ($status ?? '') !== '')
            <a href="{{ route('applicants.index') }}" class="px-3 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">Reset</a>
            @endif
        </div>
    </form>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-700 border-b border-gray-200">
                <tr>
                    <th class="p-3 text-left font-medium">Applicant</th>
                    <th class="p-3 text-left font-medium">Email</th>
                    <th class="p-3 text-left font-medium">Phone</th>
                    <th class="p-3 text-left font-medium">Organization</th>
                    <th class="p-3 text-left font-medium">Status</th>
                    <th class="p-3 text-left font-medium">Verified</th>
                    <th class="p-3 text-left font-medium">Created</th>
                    <th class="p-3 text-left font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($applicants as $applicant)
                <tr class="hover:bg-gray-50">
                    <td class="p-3">
                        <div class="text-sm font-medium text-gray-900">{{ $applicant->full_name }}</div>
                        <div class="text-xs text-gray-500">{{ $applicant->national_id_number }}</div>
                    </td>
                    <td class="p-3">{{ $applicant->email }}</td>
                    <td class="p-3">{{ $applicant->phone }}</td>
                    <td class="p-3">
                        <div>{{ $applicant->position ?? '—' }}</div>
                        <div class="text-xs text-gray-500">{{ $applicant->organization_name ?? '—' }}</div>
                    </td>
                    <td class="p-3">
                        <span
                            class="px-2 py-0.5 rounded text-xs font-medium {{ $applicant->is_active ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-yellow-100 text-yellow-700 border border-yellow-200' }}">
                            {{ $applicant->is_active ? __('app.Active') : __('app.Inactive') }}
                        </span>
                    </td>
                    <td class="p-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            {{ $applicant->hasVerifiedEmail()
                                ? 'bg-emerald-100 text-emerald-700 border border-emerald-200'
                                : 'bg-yellow-100 text-yellow-700 border border-yellow-200' }}">
                            {{ $applicant->hasVerifiedEmail() ? 'Verified' : 'Unverified' }}
                        </span>
                    </td>
                    <td class="p-3 text-gray-600">{{ optional($applicant->created_at)->format('M d, Y') }}</td>
                    <td class="p-3">
                        @if(auth()->user()?->hasPermission('applicants.manage'))
                        <div class="flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('applicants.status.update', $applicant) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_active" value="1">
                                <button type="submit"
                                    class="px-3 py-1 rounded text-xs font-medium bg-green-600 text-white hover:bg-green-700 disabled:opacity-40"
                                    @disabled($applicant->is_active)>
                                    {{ __('app.Activate') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('applicants.status.update', $applicant) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_active" value="0">
                                <button type="submit"
                                    class="px-3 py-1 rounded text-xs font-medium bg-red-600 text-white hover:bg-red-700 disabled:opacity-40"
                                    @disabled(!$applicant->is_active)>
                                    {{ __('app.Deactivate') }}
                                </button>
                            </form>
                        </div>
                        @else
                        <span class="text-xs text-gray-600">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="p-6 text-center text-gray-500">No applicants found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $applicants->links() }}</div>
</x-admin-layout>
