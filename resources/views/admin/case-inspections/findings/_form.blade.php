@csrf
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.findings.form.request') }}</label>
        <select name="case_inspection_request_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            <option value="">{{ __('case_inspections.findings.form.select_request') }}</option>
            @foreach($requests as $req)
            <option value="{{ $req->id }}" @selected(old('case_inspection_request_id', $prefillRequestId ?? $finding->case_inspection_request_id ?? null) == $req->id)>
                {{ optional($req->request_date)->format('Y-m-d') }} - {{ $req->case?->case_number }} - {{ $req->subject }}
            </option>
            @endforeach
        </select>
        @error('case_inspection_request_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.findings.form.finding_date') }}</label>
            <input type="date" name="finding_date" value="{{ old('finding_date', optional($finding->finding_date ?? null)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            @error('finding_date')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.findings.form.severity') }}</label>
            @php $sev = old('severity', $finding->severity ?? 'medium'); @endphp
            <select name="severity" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                @foreach(['low', 'medium', 'high', 'critical'] as $k)
                <option value="{{ $k }}" @selected($sev === $k)>{{ __('case_inspections.severity.' . $k) }}</option>
                @endforeach
            </select>
            @error('severity')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.findings.form.title') }}</label>
        <input type="text" name="title" value="{{ old('title', $finding->title ?? '') }}"
            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
        @error('title')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.findings.form.details') }}</label>
        <textarea name="details" rows="7"
            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>{{ old('details', $finding->details ?? '') }}</textarea>
        @error('details')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.findings.form.recommendation') }}</label>
        <textarea name="recommendation" rows="5"
            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('recommendation', $finding->recommendation ?? '') }}</textarea>
        @error('recommendation')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>
