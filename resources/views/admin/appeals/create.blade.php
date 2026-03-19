<x-admin-layout title="New Appeal">
    @section('page_header','New Appeal')

    <div class="enterprise-page max-w-4xl">
        <div class="enterprise-header">
            <h1 class="enterprise-title">Create Appeal</h1>
            <p class="enterprise-subtitle">Register a new appeal against a court case with formal grounds.</p>
        </div>

        <form method="POST" action="{{ route('appeals.store') }}" class="enterprise-panel">
            @csrf
            <div class="enterprise-panel-body space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Case</label>
                    <select name="court_case_id" class="ui-select">
                        @foreach($cases as $c)
                        <option value="{{ $c->id }}" @selected(old('court_case_id')==$c->id)>
                            {{ $c->case_number }} - {{ $c->title }}
                        </option>
                        @endforeach
                    </select>
                    @error('court_case_id') <div class="text-rose-600 text-sm mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                    <input name="title" value="{{ old('title') }}" class="ui-input">
                    @error('title') <div class="text-rose-600 text-sm mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Grounds</label>
                    <textarea name="grounds" rows="6" class="ui-textarea">{{ old('grounds') }}</textarea>
                </div>
            </div>

            <div class="enterprise-panel-header">
                <a href="{{ route('appeals.index') }}" class="btn btn-outline">Cancel</a>
                <button class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</x-admin-layout>
