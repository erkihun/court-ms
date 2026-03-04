@csrf
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.findings.form.request') }}</label>
        <select name="case_inspection_request_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            <option value="">{{ __('case_inspections.findings.form.select_request') }}</option>
            @foreach($requests as $req)
            <option value="{{ $req->id }}" @selected(old('case_inspection_request_id', $prefillRequestId ?? $finding->case_inspection_request_id ?? null) == $req->id)>
                {{ \App\Support\EthiopianDate::format($req->request_date) }} - {{ $req->case?->case_number }} - {{ $req->subject }}
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
        <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.findings.form.attachment_pdf') }}</label>
        <input type="file" name="attachment_pdf" accept="application/pdf"
            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        <p class="mt-1 text-xs text-gray-500">{{ __('case_inspections.findings.form.attachment_hint') }}</p>
        @if(!empty($finding?->attachment_path))
        <p class="mt-1 text-xs">
            <a href="{{ route('case-inspection-findings.attachment', $finding) }}" target="_blank" rel="noopener noreferrer" class="text-blue-700 hover:text-blue-800 underline">
                {{ __('case_inspections.findings.form.current_attachment') }}: {{ $finding->attachment_original_name ?? basename($finding->attachment_path) }}
            </a>
        </p>
        @endif
        @error('attachment_pdf')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>
