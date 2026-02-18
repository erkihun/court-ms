<x-applicant-layout :title="__('cases.edit_title', ['no' => $case->case_number])">
    @php
        $reviewStatus = $case->review_status ?? null;
        $reviewerApproved = $reviewStatus === 'accepted';
        $editable = ($case->status === 'pending') && !$reviewerApproved;
    @endphp
    <style>
        .tiny-content p,
        .tiny-content div,
        .tiny-content li,
        .tiny-content td,
        .tiny-content th,
        .tiny-content blockquote {
            text-align: justify;
            text-justify: inter-word;
        }
    </style>

    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-semibold text-slate-900">
            {{ __('cases.edit_title', ['no' => $case->case_number]) }}
        </h1>
        <p class="mt-1  text-slate-500">
            {{ __('cases.hints.edit_page_subtitle') ?? __('cases.hints.edit_only_pending') }}
        </p>
    </div>

    @if ($errors->any())
    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
        <div class="font-semibold mb-1">{{ __('cases.please_fix_errors') }}</div>
        <ul class="list-disc list-inside mt-1 space-y-0.5">
            @foreach ($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: main edit form --}}
        <div class="lg:col-span-2">
            <form method="POST"
                action="{{ route('applicant.cases.update', $case->id) }}"
                enctype="multipart/form-data"
                x-data="{
                      canEdit: {{ $editable ? 'true' : 'false' }},
                      docRows: 1,
                      witRows: 1
                  }">
                @csrf
                @method('PATCH')

                {{-- Header / status --}}
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div>
                                <div class="text-xs text-slate-500">{{ __('cases.case_number_short') }}</div>
                                <div class="text-xl font-semibold tracking-tight text-slate-900">
                                    {{ $case->case_number }}
                                </div>
                            </div>

                            @php
                            $status = $case->status;
                            $statusBase = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border capitalize';
                            $statusClass = match(true) {
                            $status === 'pending' => $statusBase.' bg-orange-50 text-orange-700 border-orange-200',
                            $status === 'active' => $statusBase.' bg-blue-50 text-blue-700 border-blue-200',
                            in_array($status, ['closed','dismissed']) => $statusBase.' bg-slate-100 text-slate-700 border-slate-200',
                            default => $statusBase.' bg-slate-50 text-slate-700 border-slate-200',
                            };
                            @endphp

                            <span class="{{ $statusClass }}">
                                {{ __('cases.status.' . $case->status) }}
                            </span>
                        </div>

                        <a href="{{ route('applicant.cases.show', $case->id) }}"
                            class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-1 focus:ring-slate-400">
                            {{ __('cases.back_to_case') }}
                        </a>
                    </div>

                    @unless($editable)
                    <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2  text-amber-800">
                        @if($case->status !== 'pending')
                            {{ __('cases.not_pending_readonly') }}
                        @else
                            {{ __('cases.review_notice') }}
                        @endif
                    </div>
                    @endunless
                </div>

                {{-- Case details --}}
                <div class="mt-6 rounded-xl border border-slate-200 bg-white p-5 space-y-5 shadow-sm">
                    <h2 class=" font-semibold text-slate-800">
                        {{ __('cases.section.case_details') }}
                    </h2>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block  font-medium text-slate-700 mb-1">
                                {{ __('cases.labels.title') }} <span class="text-red-600">*</span>
                            </label>
                            <input
                                name="title"
                                value="{{ old('title', $case->title) }}"
                                required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5  text-slate-900
                                       focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600
                                       disabled:bg-slate-100 disabled:text-slate-500"
                                :disabled="!canEdit" {{ $editable ? '' : 'disabled' }}>
                            @error('title')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block  font-medium text-slate-700 mb-1">
                                {{ __('cases.labels.filing_date') }} <span class="text-red-600">*</span>
                            </label>
                            <input
                                type="date"
                                name="filing_date"
                                value="{{ old('filing_date', $case->filing_date) }}"
                                required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5  text-slate-900
                                       focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600
                                       disabled:bg-slate-100 disabled:text-slate-500"
                                :disabled="!canEdit" {{ $editable ? '' : 'disabled' }}>
                            @error('filing_date')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div x-data="{
                            prefix: '{{ substr(preg_replace('/[^A-Za-z0-9]/','', $case->caseType->prefix ?? $case->caseType->name ?? ''),0,4) ?: 'CASE' }}'.toUpperCase(),
                            updatePrefix() {
                                const sel = $refs.caseTypeSelect;
                                const opt = sel.options[sel.selectedIndex];
                                const pref = opt?.dataset?.prefix || '';
                                const cleaned = (pref.match(/[A-Za-z0-9]+/g) || []).join('');
                                this.prefix = (cleaned.slice(0,4) || 'CASE').toUpperCase();
                            }
                        }">
                            <label class="block  font-medium text-slate-700 mb-1">
                                {{ __('cases.labels.case_type') }} <span class="text-red-600">*</span>
                            </label>
                            <select
                                x-ref="caseTypeSelect"
                                @change="updatePrefix()"
                                name="case_type_id"
                                required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5  text-slate-900 bg-white
                                       focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600
                                       disabled:bg-slate-100 disabled:text-slate-500"
                                :disabled="!canEdit" {{ $editable ? '' : 'disabled' }}>
                                <option value="">{{ __('cases.placeholders.select') }}</option>
                                @foreach($types as $t)
                                <option value="{{ $t->id }}" data-prefix="{{ $t->prefix ?? $t->name }}" @selected(old('case_type_id', $case->case_type_id) == $t->id)>{{ $t->name }}</option>
                                @endforeach
                            </select>
                            @error('case_type_id')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                            <p class="text-xs text-slate-500 mt-2">
                                {{ __('Case number format') }}:
                                <span class="font-mono text-slate-700" x-text="`${prefix}/00001/{{ now()->format('y') }}`"></span>
                                (type prefix / 5-digit sequence / last 2 digits of year).
                            </p>
                        </div>
                    </div>

                    {{-- Respondent --}}
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block  font-medium text-slate-700 mb-1">
                                {{ __('cases.labels.respondent_name') }}
                            </label>
                            <input
                                name="respondent_name"
                                value="{{ old('respondent_name', $case->respondent_name) }}"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5  text-slate-900
                                       focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600
                                       disabled:bg-slate-100 disabled:text-slate-500"
                                :disabled="!canEdit" {{ $editable ? '' : 'disabled' }}>
                            @error('respondent_name')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block  font-medium text-slate-700 mb-1">
                                {{ __('cases.labels.respondent_address') }}
                            </label>
                            <input
                                name="respondent_address"
                                value="{{ old('respondent_address', $case->respondent_address) }}"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5  text-slate-900
                                       focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600
                                       disabled:bg-slate-100 disabled:text-slate-500"
                                :disabled="!canEdit" {{ $editable ? '' : 'disabled' }}>
                            @error('respondent_address')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <label class="block  font-medium text-slate-700">
                                {{ __('cases.labels.description') }} <span class="text-red-600">*</span>
                            </label>
                        </div>
                        <textarea
                            id="editor-description"
                            name="description"
                            rows="16"
                            class="mt-1 w-full rounded-lg border border-slate-300  text-slate-900
                                   disabled:bg-slate-100 disabled:text-slate-500"
                            :disabled="!canEdit" {{ $editable ? '' : 'disabled' }}>{{ old('description', $case->description) }}</textarea>
                        @error('description')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Relief --}}
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <label class="block  font-medium text-slate-700">
                                {{ __('cases.labels.relief_requested') }}
                            </label>
                        </div>
                        <textarea
                            id="editor-relief"
                            name="relief_requested"
                            rows="12"
                            class="mt-1 w-full rounded-lg border border-slate-300  text-slate-900
                                   disabled:bg-slate-100 disabled:text-slate-500"
                            :disabled="!canEdit" {{ $editable ? '' : 'disabled' }}>{{ old('relief_requested', $case->relief_requested) }}</textarea>
                        @error('relief_requested')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror

                        <label class="flex items-start gap-2  text-slate-700 mt-3">
                            <input type="checkbox" name="certify_appeal" value="1"
                                class="mt-1 rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                                {{ old('certify_appeal', true) ? 'checked' : '' }}
                                :disabled="!canEdit" {{ $editable ? '' : 'disabled' }} required>
                            <span>I certify the validity of my appeal in accordance with F/S/S/No. 92.</span>
                        </label>
                        @error('certify_appeal')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Add NEW documents --}}
                <div class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class=" font-semibold text-slate-800">
                            {{ __('cases.section.add_documents') }}
                        </h2>
                        <button type="button" @click="docRows++"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-md bg-orange-500 text-white font-medium hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="!canEdit">
                            {{ __('cases.buttons.add_row') }}
                        </button>
                    </div>

                    <template x-for="i in docRows" :key="'doc'+i">
                        <div class="mt-3 grid md:grid-cols-2 gap-3">
                            <input
                                name="evidence_titles[]"
                                :placeholder="`{{ __('cases.placeholders.document_title') }}`"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5  text-slate-900
                                       focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600
                                       disabled:bg-slate-100 disabled:text-slate-500"
                                :disabled="!canEdit">
                            <input
                                type="file"
                                name="evidence_files[]"
                                accept="application/pdf"
                                class="w-full rounded-lg border border-slate-300 px-2.5 py-2  text-slate-900 bg-white
                                       focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500
                                       disabled:bg-slate-100 disabled:text-slate-500"
                                :disabled="!canEdit">
                        </div>
                    </template>
                    @error('evidence_files.*')
                    <div class="text-xs text-red-600 mt-2">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Add NEW witnesses --}}
                <div class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class=" font-semibold text-slate-800">
                            {{ __('cases.section.witnesses') }}
                        </h2>
                        <button type="button" @click="witRows++"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-md bg-orange-500 text-white font-medium hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="!canEdit">
                            {{ __('cases.buttons.add_witness') }}
                        </button>
                    </div>

                    @php
                    $oldWitnesses = old('witnesses', []);
                    $oldCount = is_array($oldWitnesses) ? count($oldWitnesses) : 0;
                    @endphp
                    <template x-if="{{ $oldCount ?: 0 }} > 0">
                        <div x-init="witRows = {{ $oldCount }}"></div>
                    </template>

                    <template x-for="i in witRows" :key="'w'+i">
                        <div class="grid md:grid-cols-6 gap-3 mb-3">
                            <input
                                :name="'witnesses['+(i-1)+'][full_name]'"
                                value="{{ old('witnesses.0.full_name') }}"
                                :placeholder="`{{ __('cases.placeholders.full_name') }}`"
                                class="px-3 py-2.5 rounded-lg bg-white border border-slate-300  text-slate-900 md:col-span-2
                                       focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600
                                       disabled:bg-slate-100 disabled:text-slate-500"
                                :disabled="!canEdit">

                            <input
                                :name="'witnesses['+(i-1)+'][phone]'"
                                value="{{ old('witnesses.0.phone') }}"
                                :placeholder="`{{ __('cases.placeholders.phone') }}`"
                                class="px-3 py-2.5 rounded-lg bg-white border border-slate-300  text-slate-900
                                       focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600
                                       disabled:bg-slate-100 disabled:text-slate-500"
                                :disabled="!canEdit">

                            <input
                                type="email"
                                :name="'witnesses['+(i-1)+'][email]'"
                                value="{{ old('witnesses.0.email') }}"
                                :placeholder="`{{ __('cases.placeholders.email') }}`"
                                class="px-3 py-2.5 rounded-lg bg-white border border-slate-300  text-slate-900
                                       focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600
                                       disabled:bg-slate-100 disabled:text-slate-500"
                                :disabled="!canEdit">

                            <input
                                :name="'witnesses['+(i-1)+'][address]'"
                                value="{{ old('witnesses.0.address') }}"
                                :placeholder="`{{ __('cases.placeholders.address') }}`"
                                class="px-3 py-2.5 rounded-lg bg-white border border-slate-300  text-slate-900 md:col-span-2
                                       focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-600
                                       disabled:bg-slate-100 disabled:text-slate-500"
                                :disabled="!canEdit">
                        </div>
                    </template>

                    @error('witnesses.*.full_name')
                    <div class="text-xs text-red-600 -mt-1">{{ $message }}</div>
                    @enderror
                    @error('witnesses.*.email')
                    <div class="text-xs text-red-600 -mt-1">{{ $message }}</div>
                    @enderror
                    @error('witnesses_duplicate_phone')
                    <div class="text-xs text-red-600 -mt-1">{{ $message }}</div>
                    @enderror
                    @error('witnesses_duplicate_email')
                    <div class="text-xs text-red-600 -mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <label class="mt-4 flex items-start gap-2  text-slate-700">
                    <input type="checkbox" name="certify_evidence" value="1"
                        class="mt-1 rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                        {{ old('certify_evidence', true) ? 'checked' : '' }}
                        :disabled="!canEdit" {{ $editable ? '' : 'disabled' }} required>
                    <span>I certify that the evidence I have presented is true in accordance with F.S./S.H./No. 92.</span>
                </label>
                @error('certify_evidence')
                <div class="text-xs text-red-600 -mt-1">{{ $message }}</div>
                @enderror

                {{-- Submit --}}
                <div class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    @if($editable)
                    <button
                        class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-orange-500 px-4 py-2.5  font-semibold text-white hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-1">
                        {{ __('cases.buttons.save_changes') }}
                    </button>
                    <p class="mt-2 text-xs text-slate-500">
                        {{ __('cases.hints.edit_only_pending') }}
                    </p>
                    @else
                    <button
                        class="w-full rounded-lg bg-slate-300 px-4 py-2.5  font-semibold text-slate-700 cursor-not-allowed"
                        disabled>
                        {{ __('cases.buttons.save_changes') }}
                    </button>
                    <p class="mt-2 text-xs text-slate-500">
                        @if($case->status !== 'pending')
                            {{ __('cases.hints.editing_disabled') }}
                        @else
                            {{ __('cases.review_notice') }}
                        @endif
                    </p>
                    @endif
                </div>
            </form>
        </div>

        {{-- RIGHT: delete actions --}}
        <aside class="space-y-6">

            {{-- Existing documents --}}
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class=" font-semibold text-slate-800">
                        {{ __('cases.section.existing_documents') }}
                    </h3>
                    <span class="text-[11px] text-slate-500">
                        {{ ($docs ?? collect())->count() }} {{ __('cases.total_suffix') }}
                    </span>
                </div>

                @if(($docs ?? collect())->isEmpty())
                <div class="mt-2  text-slate-500">
                    {{ __('cases.empty.no_documents') }}
                </div>
                @else
                <div class="overflow-x-auto -mx-3 md:mx-0">
                    <table class="min-w-full  border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600">
                                <th class="text-left font-medium px-3 py-2 border-b border-slate-200">
                                    {{ __('cases.table.title') }}
                                </th>
                                <th class="text-right font-medium px-3 py-2 border-b border-slate-200 w-40">
                                    {{ __('cases.table.actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($docs as $d)
                            @php
                            $docPath = $d->file_path ?? $d->path ?? null;
                                $docUrl = $docPath ? route('applicant.cases.evidences.download', ['id' => $case->id, 'evidenceId' => $d->id]) : null;
                            $docTitle = $d->title ?? ($docPath ? basename($docPath) : __('cases.labels.document'));
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 font-medium text-slate-900">
                                    @if($editable)
                                    <form method="POST"
                                        action="{{ route('applicant.cases.update', $case->id) }}"
                                        enctype="multipart/form-data"
                                        class="space-y-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="_evidence_update" value="1">
                                        <input type="hidden" name="evidence_id" value="{{ $d->id }}">
                                        <input
                                            type="text"
                                            name="title"
                                            value="{{ $docTitle }}"
                                            class="w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="{{ __('cases.placeholders.document_title') }}">
                                        <input
                                            type="file"
                                            name="file"
                                            accept="application/pdf"
                                            class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs text-slate-900 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <button
                                            class="px-2.5 py-1.5 rounded-md bg-blue-600 text-white text-xs hover:bg-blue-700">
                                            Update
                                        </button>
                                    </form>
                                    @else
                                    {{ $docTitle }}
                                    @if(!$docPath)
                                    <span class="ml-2 text-[11px] text-slate-500">
                                        ({{ __('cases.inline') }})
                                    </span>
                                    @endif
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        @if($docUrl)
                                        <a href="{{ $docUrl }}" target="_blank"
                                            class="px-2.5 py-1.5 rounded-md border border-slate-300 text-xs text-slate-700 bg-white hover:bg-slate-50">
                                            {{ __('cases.table.view') }}
                                        </a>
                                        @endif
                                        @if($editable)
                                        <form method="POST"
                                            action="{{ route('applicant.cases.evidences.delete', ['id' => $case->id, 'evidenceId' => $d->id]) }}"
                                            onsubmit="return confirm('{{ __('cases.confirm.remove_document') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                class="px-2.5 py-1.5 rounded-md bg-red-600 text-white text-xs hover:bg-red-700">
                                                {{ __('cases.general.delete') }}
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Existing witnesses --}}
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class=" font-semibold text-slate-800">
                        {{ __('cases.section.existing_witnesses') }}
                    </h3>
                    <span class="text-[11px] text-slate-500">
                        {{ ($witnesses ?? collect())->count() }} {{ __('cases.total_suffix') }}
                    </span>
                </div>

                @if(($witnesses ?? collect())->isEmpty())
                <div class="mt-2  text-slate-500">
                    {{ __('cases.empty.no_witnesses') }}
                </div>
                @else
                <div class="overflow-x-auto -mx-3 md:mx-0">
                    <table class="min-w-full  border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600">
                                <th class="text-left font-medium px-3 py-2 border-b border-slate-200">
                                    {{ __('cases.labels.name') }}
                                </th>
                                <th class="text-left font-medium px-3 py-2 border-b border-slate-200">
                                    {{ __('cases.labels.phone') }}
                                </th>
                                <th class="text-left font-medium px-3 py-2 border-b border-slate-200">
                                    {{ __('cases.labels.email') }}
                                </th>
                                <th class="text-left font-medium px-3 py-2 border-b border-slate-200">
                                    {{ __('cases.labels.address') }}
                                </th>
                                @if($editable)
                                <th class="text-right font-medium px-3 py-2 border-b border-slate-200 w-24">
                                    {{ __('cases.table.actions') }}
                                </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($witnesses as $w)
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2 font-medium text-slate-900">
                                    {{ $w->full_name }}
                                </td>
                                <td class="px-3 py-2 text-slate-700">
                                    {{ $w->phone ?: '—' }}
                                </td>
                                <td class="px-3 py-2">
                                    @if(!empty($w->email))
                                    <a href="mailto:{{ $w->email }}" class="text-blue-700 hover:underline">
                                        {{ $w->email }}
                                    </a>
                                    @else
                                    <span class="text-slate-500">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-slate-700">
                                    {{ $w->address ?: '—' }}
                                </td>
                                @if($editable)
                                <td class="px-3 py-2 text-right">
                                    <form method="POST"
                                        action="{{ route('applicant.cases.witnesses.delete', ['id' => $case->id, 'witnessId' => $w->id]) }}"
                                        onsubmit="return confirm('{{ __('cases.confirm.remove_witness') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            class="px-2.5 py-1.5 rounded-md bg-red-600 text-white text-xs hover:bg-red-700">
                                            {{ __('cases.general.delete') }}
                                        </button>
                                    </form>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

        </aside>
    </div>

    {{-- LOCAL TinyMCE --}}
    <script src="{{ asset('vendor/tinymce/tinymce.min.js') }}" referrerpolicy="origin"></script>
    <script>
        (function() {
            const TINY_BASE = "{{ asset('vendor/tinymce') }}";

            const common = {
                base_url: TINY_BASE,
                suffix: '.min',
                license_key: 'gpl',
                branding: false,
                promotion: false,
                menubar: true,

                // Show all toolbar items (no overflow chevron)
                toolbar_mode: 'wrap',
                toolbar_sticky: true,

                plugins: 'lists link table code image advlist charmap fullscreen',
                toolbar: [
                    'undo redo |  fontfamily fontsize | bold italic underline strikethrough removeformat',
                    '| forecolor backcolor | alignleft aligncenter alignright alignjustify',
                    '| numlist bullist outdent indent  | fullscreen code'
                ].join(' '),

                // Make every new block justified
                forced_root_block: 'p',
                forced_root_block_attrs: {
                    style: 'text-align: justify;'
                },

                // Enforce justified look in the editor for common blocks
                content_style: `
            body, p, div, li, td, th, blockquote { text-align: justify; text-justify: inter-word; }
            table{width:100%;border-collapse:collapse}
            td,th{border:1px solid #ddd;padding:4px}
            body{font-size:14px;line-height:1.5}
        `,

                // Fix pasted content that brings its own alignment
                paste_postprocess(plugin, args) {
                    const blocks = args.node.querySelectorAll('p,div,li,td,th,blockquote');
                    blocks.forEach(el => {
                        el.style.textAlign = 'justify';
                    });
                },

                // Fixed-height editors
                resize: false,
                statusbar: true,

                setup(editor) {
                    // Ensure initial content shows as justified on init
                    editor.on('init', () => {
                        editor.execCommand('JustifyFull');
                    });
                }
            };

            // CASE DETAILS — taller
            tinymce.init({
                ...common,
                selector: '#editor-description',
                height: 520,
                min_height: 520,
                max_height: 520
            });

            // RELIEF — shorter
            tinymce.init({
                ...common,
                selector: '#editor-relief',
                height: 380,
                min_height: 380,
                max_height: 380
            });
        })();
    </script>

</x-applicant-layout>
