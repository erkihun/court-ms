{{-- resources/views/cases/partials/messages-section.blade.php --}}
<section id="messages-section" x-show="activeSection==='messages'" x-transition
    class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm space-y-4">
    <div class="flex items-center justify-between border-b border-gray-200 pb-3">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            {{ __('cases.messages_section.title') }}
        </h3>
        <span class="text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2.5 py-1">{{ ($messages ?? collect())->count() }} {{ __('cases.messages.total') }}</span>
    </div>

    <div class="space-y-4 max-h-96 overflow-auto pr-2">
        @forelse($messages as $m)
        @php
        $fromAdmin = !is_null($m->sender_user_id);
        $fromApplicant = !is_null($m->sender_applicant_id);
        $who = $fromAdmin
        ? ($m->admin_name ?: __('cases.messages.court_staff'))
        : ($fromApplicant ? trim(($m->first_name ?? '').' '.($m->last_name ?? '')) : __('cases.messages_section.system'));
        @endphp
        <div class="rounded-lg border border-gray-200 p-4 {{ $fromAdmin ? 'bg-blue-50 ml-8' : 'bg-gray-50 mr-8' }}">
            <div class="text-xs text-gray-600 mb-2 flex items-center justify-between">
                <span class="font-medium text-gray-900">{{ $who }}</span>
                <span>{{ \Illuminate\Support\Carbon::parse($m->created_at)->format('M d, Y H:i') }}</span>
            </div>
            <div class="whitespace-pre-wrap text-gray-800 text-sm">{{ $m->body }}</div>
        </div>
        @empty
        <div class="text-gray-500 text-sm border border-dashed border-gray-300 rounded-lg p-6 text-center bg-gray-50">
            {{ __('cases.messages_section.no_messages') }}
        </div>
        @endforelse
    </div>

    @if($canEditStatus)
    <form method="POST" action="{{ route('cases.messages.post', $case->id) }}" class="pt-4 border-t border-gray-200 space-y-3"
        @submit.prevent="submitSectionForm($event, '#messages-section')">
        @csrf
        <label class="block text-sm font-medium text-gray-700">{{ __('cases.messages_section.reply_to_applicant') }}</label>
        <textarea name="body" rows="3" class="w-full px-4 py-3 rounded-lg bg-white text-gray-900 border border-gray-300" placeholder="{{ __('cases.messages_section.write_message_placeholder') }}">{{ old('body') }}</textarea>
        @error('body') <p class="text-red-600 text-sm p-2 bg-red-50 rounded-lg border border-red-200">{{ $message }}</p> @enderror
        <button class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium">
            {{ __('cases.messages_section.send_message') }}
        </button>
    </form>
    @endif
</section>