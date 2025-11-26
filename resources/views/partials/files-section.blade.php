{{-- resources/views/cases/partials/files-section.blade.php --}}

@php
$user = auth()->user();
$canEditStatus = function_exists('userHasPermission')
? userHasPermission('cases.edit')
: ($user && method_exists($user, 'hasPermission') ? $user->hasPermission('cases.edit') : false);
@endphp

<section id="files-section"
    x-show="activeSection === 'uploaded-files'"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-4"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm space-y-4">

    <div class="flex items-center justify-between border-b border-gray-200 pb-3">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            {{ __('cases.files.title') }}
        </h3>
        <span class="text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2.5 py-1">
            {{ ($files ?? collect())->count() }} {{ __('cases.files.total') }}
        </span>
    </div>

    @if($canEditStatus)
    <form method="POST"
        action="{{ route('cases.files.upload', $case->id) }}"
        enctype="multipart/form-data"
        class="mb-2 grid grid-cols-1 sm:grid-cols-[1fr_auto_auto] gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200"
        @submit.prevent="submitSectionForm($event, '#files-section')">
        @csrf
        <input name="label" placeholder="{{ __('cases.files.label_placeholder') }}"
            class="px-3 py-2.5 rounded-lg bg-white border border-gray-300 text-gray-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-150">
        <input type="file" name="file" required
            class="text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300 transition-colors duration-150">
        <button class="px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium transition-colors duration-150 flex items-center justify-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            {{ __('cases.files.upload') }}
        </button>
    </form>
    @error('file')
    <div class="text-red-600 text-sm mb-2 p-2 bg-red-50 rounded-lg border border-red-200">{{ $message }}</div>
    @enderror
    @endif

    @if(($files ?? collect())->isEmpty())
    <div
        class="text-gray-500 text-sm border border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400 mb-2" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        {{ __('cases.files.no_files') }}
    </div>
    @else
    <ul class="divide-y divide-gray-200">
        @foreach($files as $f)
        <li class="py-3 flex items-center justify-between hover:bg-gray-50 px-3 rounded-lg transition-colors duration-150">
            <div class="text-sm">
                <div class="font-medium text-gray-900 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ $f->label ?? basename($f->path) }}
                </div>
                <div class="text-xs text-gray-600 mt-1 flex items-center gap-3 flex-wrap">
                    <span>{{ $f->mime ?? 'file' }}</span>
                    <span>• {{ number_format(($f->size ?? 0)/1024,1) }} KB</span>
                    <span>• {{ \Illuminate\Support\Carbon::parse($f->created_at)->format('M d, Y H:i') }}</span>
                    @php
                    $by = $f->uploader_name ?? trim(($f->first_name ?? '').' '.($f->last_name ?? ''));
                    @endphp
                    @if($by)
                    <span>• {{ __('cases.files.uploaded_by') }} {{ $by }}</span>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ asset('storage/'.$f->path) }}" target="_blank"
                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-gray-50 text-xs text-gray-700 border border-gray-300 transition-colors duration-150 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    {{ __('cases.documents.view') }}
                </a>
                @if($canEditStatus)
                <form method="POST"
                    action="{{ route('cases.files.delete', [$case->id, $f->id]) }}"
                    onsubmit="return confirm('{{ __('cases.files.remove_confirm') }}')"
                    @submit.prevent="submitSectionForm($event, '#files-section')">
                    @csrf @method('DELETE')
                    <button class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-medium transition-colors duration-150 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        {{ __('cases.general.delete') }}
                    </button>
                </form>
                @endif
            </div>
        </li>
        @endforeach
    </ul>
    @endif
</section>