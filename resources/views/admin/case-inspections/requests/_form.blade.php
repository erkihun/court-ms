@csrf
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.requests.form.case') }}</label>
        <select name="court_case_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            <option value="">{{ __('case_inspections.requests.form.select_case') }}</option>
            @foreach($cases as $case)
            <option value="{{ $case->id }}" @selected(old('court_case_id', $prefillCaseId ?? $requestRecord->court_case_id ?? null) == $case->id)>
                {{ $case->case_number }} - {{ $case->title }}
            </option>
            @endforeach
        </select>
        @error('court_case_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.requests.form.request_date') }}</label>
            <input type="date" name="request_date" value="{{ old('request_date', optional($requestRecord->request_date ?? null)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            @error('request_date')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.requests.form.status') }}</label>
            <select name="status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                @php $statusValue = old('status', $requestRecord->status ?? 'pending'); @endphp
                @foreach(['pending', 'in_progress', 'completed', 'cancelled'] as $key)
                <option value="{{ $key }}" @selected($statusValue === $key)>{{ __('case_inspections.status.' . $key) }}</option>
                @endforeach
            </select>
            @error('status')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.requests.form.subject') }}</label>
        <input type="text" name="subject" value="{{ old('subject', $requestRecord->subject ?? '') }}"
            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
        @error('subject')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    @if(auth()->user()?->hasPermission('assign.inspections'))
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.requests.form.assigned_inspector') }}</label>
        <select name="assigned_inspector_user_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">{{ __('case_inspections.requests.form.unassigned') }}</option>
            @foreach($inspectors as $inspector)
            <option value="{{ $inspector->id }}" @selected(old('assigned_inspector_user_id', $prefillInspectorId ?? $requestRecord->assigned_inspector_user_id ?? null) == $inspector->id)>
                {{ $inspector->name }}
            </option>
            @endforeach
        </select>
        @error('assigned_inspector_user_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    @endif

    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.requests.form.request_note') }}</label>
        <textarea name="request_note" rows="6"
            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('request_note', $requestRecord->request_note ?? '') }}</textarea>
        @error('request_note')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>
