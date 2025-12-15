<x-admin-layout title="Profile">
    @section('page_header','Profile')

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-4 rounded bg-green-100 border border-green-200 text-green-700 px-3 py-2">
        {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-4 rounded bg-red-100 border border-red-200 text-red-700 px-3 py-2">
        <ul class="list-disc ml-5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="grid md:grid-cols-3 gap-6">
        @csrf
        @method('PATCH')

        {{-- Left: Basic info --}}
        <div class="md:col-span-2 space-y-4 p-4 rounded-xl border border-gray-200 bg-white shadow-sm">
            <div>
                <label class="block text-sm mb-1 text-gray-700">Name</label>
                <input name="name" value="{{ old('name', auth()->user()->name) }}"
                    class="w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
            </div>
            <div>
                <label class="block text-sm mb-1 text-gray-700">Email</label>
                <input name="email" type="email" value="{{ old('email', auth()->user()->email) }}"
                    class="w-full px-3 py-2 rounded bg-white text-gray-900 border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
            </div>
            <button class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">Save</button>
        </div>

        {{-- Right: Avatar & Signature --}}
        <div class="space-y-6">
            <div class="p-4 rounded-xl border border-gray-200 bg-white shadow-sm">
                <label class="block text-sm mb-2 text-gray-700">Avatar (JPG/PNG/WebP, ≤ 2MB)</label>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-gray-100 border border-gray-300 overflow-hidden grid place-items-center">
                        @if(auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" class="w-full h-full object-cover" alt="Avatar">
                        @else
                        <span class="text-lg font-semibold text-gray-600">
                            {{ strtoupper(substr(auth()->user()->name ?? 'A',0,1)) }}
                        </span>
                        @endif
                    </div>
                    <input type="file" name="avatar" accept="image/*"
                        class="text-sm file:mr-3 file:px-3 file:py-1.5 file:rounded file:bg-white file:border file:border-gray-300 file:text-gray-700 hover:file:bg-gray-50">
                </div>
            </div>

            <div class="p-4 rounded-xl border border-gray-200 bg-white shadow-sm">
                <label class="block text-sm mb-2 text-gray-700">Signature (PNG/WebP preferred, ≤ 2MB)</label>
                <div class="space-y-2">
                    @if(auth()->user()->signature_url)
                    <img src="{{ auth()->user()->signature_url }}" class="max-h-20 border border-gray-200" alt="Signature">
                    @else
                    <div class="text-xs text-gray-500">No signature uploaded.</div>
                    @endif
                    <input type="file" name="signature" accept="image/png,image/webp,image/jpeg"
                        class="text-sm file:mr-3 file:px-3 file:py-1.5 file:rounded file:bg-white file:border file:border-gray-300 file:text-gray-700 hover:file:bg-gray-50">
                </div>
            </div>
        </div>
    </form>

    <div class="mt-8 p-4 rounded-xl border border-gray-200 bg-white shadow-sm">
        @include('admin.profile.partials.update-password-form')
    </div>
</x-admin-layout>
