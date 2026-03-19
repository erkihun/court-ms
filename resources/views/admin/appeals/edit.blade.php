<x-admin-layout :title="'Edit ' . $appeal->appeal_number">
    @section('page_header','Edit Appeal')

    <div class="enterprise-page max-w-4xl">
        <div class="enterprise-header">
            <h1 class="enterprise-title">Edit Appeal</h1>
            <p class="enterprise-subtitle">Update appeal title and grounds before final submission.</p>
        </div>

        <form method="POST" action="{{ route('appeals.update',$appeal->id) }}" class="enterprise-panel">
            @csrf
            @method('PATCH')
            <div class="enterprise-panel-body space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                    <input name="title" value="{{ old('title',$appeal->title) }}" class="ui-input">
                    @error('title') <div class="text-rose-600 text-sm mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Grounds</label>
                    <textarea name="grounds" rows="6" class="ui-textarea">{{ old('grounds',$appeal->grounds) }}</textarea>
                </div>
            </div>

            <div class="enterprise-panel-header">
                <a href="{{ route('appeals.show',$appeal->id) }}" class="btn btn-outline">Cancel</a>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</x-admin-layout>
