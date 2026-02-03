@csrf
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.form_case_label') }}</label>
        <select name="court_case_id"
            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
            <option value="">{{ __('case_inspections.form_case_select') }}</option>
            @foreach($cases as $case)
            <option value="{{ $case->id }}" @selected(old('court_case_id', $prefillCaseId ?? $caseInspection->court_case_id ?? null) == $case->id)>
                {{ $case->case_number }} — {{ $case->title }}
            </option>
            @endforeach
        </select>
        @error('court_case_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.form_date_label') }}</label>
        <input type="date" name="inspection_date" value="{{ old('inspection_date', optional($caseInspection->inspection_date ?? null)->format('Y-m-d')) }}"
            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
        @error('inspection_date')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.form_summary_label') }}</label>
        <input type="text" name="summary" value="{{ old('summary', $caseInspection->summary ?? '') }}"
            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
        @error('summary')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    @if(auth()->user()?->hasPermission('assign.inspections'))
    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.form_inspector_label') }}</label>
        <select name="inspected_by_user_id"
            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
            <option value="">{{ __('case_inspections.form_inspector_select') }}</option>
            @foreach($inspectors as $user)
            <option value="{{ $user->id }}" @selected(old('inspected_by_user_id', $prefillInspectorId ?? $caseInspection->inspected_by_user_id ?? null) == $user->id)>
                {{ $user->name }}
            </option>
            @endforeach
        </select>
        @error('inspected_by_user_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    @endif

    <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('case_inspections.form_details_label') }}</label>
        <textarea id="case-inspection-details" name="details" rows="10"
            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">{{ old('details', $caseInspection->details ?? '') }}</textarea>
        @error('details')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>
