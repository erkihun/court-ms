<x-admin-layout title="Landing Page Manager">
@section('page_header', 'Landing Page Manager')

<div x-data="{ tab: 'slides' }" class="space-y-6">

    {{-- Tab bar --}}
    <div class="flex flex-wrap gap-1 rounded-2xl border border-slate-200 bg-slate-100 p-1 w-fit">
        @foreach(['slides' => 'Hero Slides', 'timeline' => 'Timeline', 'services' => 'Services', 'resources' => 'Resources', 'faqs' => 'FAQ', 'footer' => 'Footer', 'sections' => 'Sections'] as $key => $label)
        <button @click="tab = '{{ $key }}'"
                :class="tab === '{{ $key }}' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                class="rounded-xl px-4 py-2 text-sm font-semibold transition">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- =====================================================================
         TAB: HERO SLIDES
    ===================================================================== --}}
    <div x-show="tab === 'slides'" x-cloak>
        <div x-data="{ showForm: false, editing: null, form: {} }" class="space-y-4">

            {{-- Add button --}}
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Hero Slides</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Manage the full-width hero carousel on the landing page.</p>
                </div>
                <button @click="showForm = !showForm; editing = null; form = { bg_style: 'blue' }"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Slide
                </button>
            </div>

            {{-- Add / Edit form --}}
            <div x-show="showForm" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                <h3 class="text-sm font-semibold text-slate-800 mb-4" x-text="editing ? 'Edit Slide' : 'New Slide'"></h3>

                <form :action="editing ? '/admin/landing/slides/' + editing + '/update' : '{{ route('admin.landing.slides.store') }}'"
                      method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input x-show="editing" type="hidden" name="_method" value="PUT">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Badge text <span class="text-slate-400">(optional)</span></label>
                            <input type="text" name="badge" x-model="form.badge" maxlength="120"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Background style <span class="text-red-500">*</span></label>
                            <select name="bg_style" x-model="form.bg_style"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="blue">Blue (default)</option>
                                <option value="orange">Orange / Warm</option>
                                <option value="emerald">Emerald / Analytics</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" x-model="form.title" required maxlength="255"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Description</label>
                        <textarea name="description" x-model="form.description" rows="3" maxlength="1000"
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Primary button label <span class="text-red-500">*</span></label>
                            <input type="text" name="primary_label" x-model="form.primary_label" required maxlength="120"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Primary button URL <span class="text-red-500">*</span></label>
                            <input type="text" name="primary_href" x-model="form.primary_href" required maxlength="500"
                                   placeholder="/applicant/register"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Secondary button label</label>
                            <input type="text" name="secondary_label" x-model="form.secondary_label" maxlength="120"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Secondary button URL</label>
                            <input type="text" name="secondary_href" x-model="form.secondary_href" maxlength="500"
                                   placeholder="/applicant/login"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    {{-- Background image --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Background image <span class="text-slate-400">(optional, max 3 MB)</span></label>
                        <input type="file" name="bg_image" accept="image/*"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-[11px] text-slate-400 mt-1">The image overlays the colour gradient. When editing, leave empty to keep the current image.</p>
                        <template x-if="editing && form.bg_image">
                            <div class="mt-2 flex items-center gap-3">
                                <img :src="'/storage/' + form.bg_image" class="h-10 rounded-lg object-cover" alt="Current image">
                                <label class="flex items-center gap-1.5 text-xs text-red-600 cursor-pointer">
                                    <input type="checkbox" name="remove_bg_image" value="1" class="rounded border-slate-300 text-red-600">
                                    Remove current image
                                </label>
                            </div>
                        </template>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="editing ? 'Update Slide' : 'Save Slide'"></span>
                        </button>
                        <button type="button" @click="showForm = false; editing = null"
                                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            {{-- Slides list --}}
            @if($slides->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-sm text-slate-500">
                No slides yet. Click <strong>Add Slide</strong> to create the first hero slide.
            </div>
            @else
            <div class="space-y-3">
                @foreach($slides as $slide)
                <div class="flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    {{-- Thumbnail or colour swatch --}}
                    <div class="flex-shrink-0 mt-1">
                        @if($slide->bg_image)
                        <img src="{{ asset('storage/' . $slide->bg_image) }}"
                             class="h-12 w-16 rounded-xl object-cover" alt="">
                        @else
                        <div class="h-10 w-10 rounded-xl
                            {{ $slide->bg_style === 'orange' ? 'bg-gradient-to-br from-orange-600 to-amber-400' : ($slide->bg_style === 'emerald' ? 'bg-gradient-to-br from-emerald-700 to-teal-400' : 'bg-gradient-to-br from-blue-700 to-blue-500') }}">
                        </div>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            @if($slide->badge)
                            <span class="rounded-full bg-blue-100 text-blue-700 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider">{{ $slide->badge }}</span>
                            @endif
                            @if(!$slide->is_active)
                            <span class="rounded-full bg-slate-100 text-slate-500 px-2 py-0.5 text-[10px] font-semibold">Hidden</span>
                            @endif
                        </div>
                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $slide->title }}</p>
                        @if($slide->description)
                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $slide->description }}</p>
                        @endif
                        <p class="text-[10px] text-slate-400 mt-1">
                            {{ $slide->primary_label }} → {{ $slide->primary_href }}
                        </p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex-shrink-0 flex items-center gap-1">
                        {{-- Toggle visibility --}}
                        <form method="POST" action="{{ route('admin.landing.slides.toggle', $slide) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 transition"
                                    title="{{ $slide->is_active ? 'Hide' : 'Show' }}">
                                @if($slide->is_active)
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                @else
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                @endif
                            </button>
                        </form>

                        {{-- Edit --}}
                        <button @click="
                                editing = {{ $slide->id }};
                                form = {
                                    badge: '{{ addslashes($slide->badge ?? '') }}',
                                    title: '{{ addslashes($slide->title) }}',
                                    description: '{{ addslashes($slide->description ?? '') }}',
                                    primary_label: '{{ addslashes($slide->primary_label) }}',
                                    primary_href: '{{ addslashes($slide->primary_href) }}',
                                    secondary_label: '{{ addslashes($slide->secondary_label ?? '') }}',
                                    secondary_href: '{{ addslashes($slide->secondary_href ?? '') }}',
                                    bg_style: '{{ $slide->bg_style }}',
                                    bg_image: '{{ $slide->bg_image ?? '' }}'
                                };
                                showForm = true;
                                $nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }))
                            "
                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition"
                            title="Edit">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>

                        {{-- Delete --}}
                        <form method="POST" action="{{ route('admin.landing.slides.destroy', $slide) }}"
                              onsubmit="return confirm('Delete this slide?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition"
                                    title="Delete">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- =====================================================================
         TAB: TIMELINE STEPS
    ===================================================================== --}}
    <div x-show="tab === 'timeline'" x-cloak>
        <div x-data="{ showForm: false, editing: null, form: {} }" class="space-y-4">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Case Processing Journey</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Steps shown in the timeline section on the landing page.</p>
                </div>
                <button @click="showForm = !showForm; editing = null; form = { color: 'blue' }"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Step
                </button>
            </div>

            <div x-show="showForm" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                <h3 class="text-sm font-semibold text-slate-800 mb-4" x-text="editing ? 'Edit Step' : 'New Step'"></h3>

                <form :action="editing ? '/admin/landing/steps/' + editing + '/update' : '{{ route('admin.landing.steps.store') }}'"
                      method="POST" class="space-y-4">
                    @csrf
                    <input x-show="editing" type="hidden" name="_method" value="PUT">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-700 mb-1">Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" x-model="form.title" required maxlength="255"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Meta label <span class="text-slate-400">(e.g. Step 1)</span></label>
                            <input type="text" name="meta" x-model="form.meta" maxlength="120"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Duration <span class="text-slate-400">(e.g. 1-3 days)</span></label>
                            <input type="text" name="duration" x-model="form.duration" maxlength="60"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Description</label>
                        <textarea name="description" x-model="form.description" rows="3" maxlength="1000"
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>

                    <div class="w-48">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Colour accent</label>
                        <select name="color" x-model="form.color"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="blue">🔵 Blue</option>
                            <option value="orange">🟠 Orange</option>
                            <option value="emerald">🟢 Emerald</option>
                            <option value="violet">🟣 Violet</option>
                            <option value="amber">🟡 Amber</option>
                        </select>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button type="submit"
                                class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                            <span x-text="editing ? 'Update Step' : 'Save Step'"></span>
                        </button>
                        <button type="button" @click="showForm = false; editing = null"
                                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 transition">Cancel</button>
                    </div>
                </form>
            </div>

            {{-- Steps list --}}
            @if($steps->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-sm text-slate-500">
                No timeline steps yet. Click <strong>Add Step</strong> to create the first step.
            </div>
            @else
            <div class="space-y-2">
                @foreach($steps as $i => $step)
                <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                    {{-- Colour dot --}}
                    <div class="flex-shrink-0 h-8 w-8 rounded-full flex items-center justify-center text-white text-xs font-bold
                        {{ match($step->color) { 'orange' => 'bg-orange-500', 'emerald' => 'bg-emerald-500', 'violet' => 'bg-violet-500', 'amber' => 'bg-amber-500', default => 'bg-blue-500' } }}">
                        {{ $i + 1 }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            @if(!$step->is_active)<span class="rounded-full bg-slate-100 text-slate-500 px-2 py-0.5 text-[10px] font-semibold">Hidden</span>@endif
                            @if($step->meta)<span class="text-[10px] text-slate-400 font-medium">{{ $step->meta }}</span>@endif
                            @if($step->duration)<span class="text-[10px] text-slate-400">· {{ $step->duration }}</span>@endif
                        </div>
                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $step->title }}</p>
                        @if($step->description)<p class="text-xs text-slate-500 line-clamp-1 mt-0.5">{{ $step->description }}</p>@endif
                    </div>

                    <div class="flex-shrink-0 flex items-center gap-1">
                        {{-- Toggle --}}
                        <form method="POST" action="{{ route('admin.landing.steps.toggle', $step) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 transition"
                                    title="{{ $step->is_active ? 'Hide' : 'Show' }}">
                                @if($step->is_active)
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                @else
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                @endif
                            </button>
                        </form>

                        {{-- Edit --}}
                        <button @click="
                                editing = {{ $step->id }};
                                form = {
                                    title: {{ json_encode($step->title) }},
                                    meta: {{ json_encode($step->meta) }},
                                    duration: {{ json_encode($step->duration) }},
                                    description: {{ json_encode($step->description) }},
                                    color: {{ json_encode($step->color) }}
                                };
                                showForm = true;
                                $nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }))
                            "
                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>

                        {{-- Delete --}}
                        <form method="POST" action="{{ route('admin.landing.steps.destroy', $step) }}"
                              onsubmit="return confirm('Delete this step?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- =====================================================================
         TAB: SERVICES
    ===================================================================== --}}
    <div x-show="tab === 'services'" x-cloak>
        <div x-data="{ showForm: false, editing: null, form: {} }" class="space-y-4">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Service Cards</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Manage the service cards shown in the Core Services section. When any cards are saved here, they replace the default built-in cards.</p>
                </div>
                <button @click="showForm = !showForm; editing = null; form = { icon_type: 'document', accent: 'blue' }"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Service
                </button>
            </div>

            <div x-show="showForm" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                <h3 class="text-sm font-semibold text-slate-800 mb-4" x-text="editing ? 'Edit Service Card' : 'New Service Card'"></h3>

                <form :action="editing ? '/admin/landing/services/' + editing + '/update' : '{{ route('admin.landing.services.store') }}'"
                      method="POST" class="space-y-4">
                    @csrf
                    <input x-show="editing" type="hidden" name="_method" value="PUT">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" x-model="form.title" maxlength="255" required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Meta label <span class="text-slate-400">(e.g. Core Service)</span></label>
                            <input type="text" name="meta" x-model="form.meta" maxlength="120"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Description</label>
                        <textarea name="description" x-model="form.description" rows="2" maxlength="1000"
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Features <span class="text-slate-400">(one per line)</span></label>
                        <textarea name="features" x-model="form.features" rows="4"
                                  placeholder="Feature one&#10;Feature two&#10;Feature three"
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Icon</label>
                            <select name="icon_type" x-model="form.icon_type"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="document">📄 Document / Upload</option>
                                <option value="calendar">📅 Calendar / Hearing</option>
                                <option value="lock">🔒 Lock / Evidence</option>
                                <option value="chart">📊 Chart / Tracking</option>
                                <option value="database">🗄️ Database</option>
                                <option value="chat">💬 Chat / Dispute</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Accent colour</label>
                            <select name="accent" x-model="form.accent"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="blue">🔵 Blue</option>
                                <option value="orange">🟠 Orange</option>
                                <option value="emerald">🟢 Emerald</option>
                                <option value="violet">🟣 Violet</option>
                                <option value="amber">🟡 Amber</option>
                                <option value="pink">🩷 Pink</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button type="submit"
                                class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">Save</button>
                        <button type="button" @click="showForm = false"
                                class="rounded-xl border border-slate-300 px-5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">Cancel</button>
                    </div>
                </form>
            </div>

            @if($services->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-sm text-slate-400">
                No service cards saved yet — the page shows the 6 built-in default cards.
            </div>
            @else
            <div class="space-y-2">
                @foreach($services as $svc)
                <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4 flex-1 min-w-0">
                        <form method="POST" action="{{ route('admin.landing.services.toggle', $svc) }}">
                            @csrf @method('PATCH')
                            <button type="submit" title="{{ $svc->is_active ? 'Deactivate' : 'Activate' }}"
                                    class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none
                                    {{ $svc->is_active ? 'bg-emerald-500' : 'bg-slate-200' }}">
                                <span class="pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow transform transition duration-200
                                    {{ $svc->is_active ? 'translate-x-4' : 'translate-x-0' }}"></span>
                            </button>
                        </form>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ $svc->title }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $svc->meta }} · {{ $svc->icon_type }} · {{ $svc->accent }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <button @click="
                                editing = {{ $svc->id }};
                                form = {
                                    title: {{ json_encode($svc->title) }},
                                    meta: {{ json_encode($svc->meta) }},
                                    description: {{ json_encode($svc->description) }},
                                    features: {{ json_encode(implode("\n", $svc->features ?? [])) }},
                                    icon_type: {{ json_encode($svc->icon_type) }},
                                    accent: {{ json_encode($svc->accent) }}
                                };
                                showForm = true;
                                $nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }))
                            "
                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <form method="POST" action="{{ route('admin.landing.services.destroy', $svc) }}"
                              onsubmit="return confirm('Delete this service card?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- =====================================================================
         TAB: RESOURCES
    ===================================================================== --}}
    <div x-show="tab === 'resources'" x-cloak>
        <div x-data="{ showForm: false, editing: null, form: {} }" class="space-y-4">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Resources &amp; Posts</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Posts, downloadable forms, documents, and links shown on the public Resources page.</p>
                </div>
                <button @click="showForm = !showForm; editing = null; form = { type: 'post', is_featured: false }"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Resource
                </button>
            </div>

            <div x-show="showForm" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                <h3 class="text-sm font-semibold text-slate-800 mb-4" x-text="editing ? 'Edit Resource' : 'New Resource'"></h3>

                <form :action="editing ? '/admin/landing/resources/' + editing + '/update' : '{{ route('admin.landing.resources.store') }}'"
                      method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input x-show="editing" type="hidden" name="_method" value="PUT">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-700 mb-1">Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" x-model="form.title" required maxlength="255"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Type <span class="text-red-500">*</span></label>
                            <select name="type" x-model="form.type"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="post">📝 Post / Update</option>
                                <option value="form">📋 Downloadable Form</option>
                                <option value="document">📄 Document</option>
                                <option value="link">🔗 External Link</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Publish date <span class="text-slate-400">(optional, schedule future)</span></label>
                            <input type="datetime-local" name="published_at" x-model="form.published_at"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Description / Summary</label>
                        <textarea name="description" x-model="form.description" rows="3" maxlength="2000"
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>

                    <div x-show="form.type === 'link'">
                        <label class="block text-xs font-medium text-slate-700 mb-1">External URL</label>
                        <input type="url" name="external_url" x-model="form.external_url" maxlength="500"
                               placeholder="https://..."
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div x-show="form.type === 'form' || form.type === 'document'">
                        <label class="block text-xs font-medium text-slate-700 mb-1">File <span class="text-slate-400">(PDF, DOCX, XLS, etc. max 20 MB)</span></label>
                        <input type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-100">
                        <template x-if="editing && form.file_path">
                            <div class="mt-2 flex items-center gap-3 text-xs text-slate-500">
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span x-text="form.file_path"></span>
                                <label class="flex items-center gap-1 text-red-600 cursor-pointer">
                                    <input type="checkbox" name="remove_file" value="1" class="rounded border-slate-300 text-red-600">
                                    Remove file
                                </label>
                            </div>
                        </template>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Cover image <span class="text-slate-400">(optional, max 2 MB)</span></label>
                        <input type="file" name="cover_image" accept="image/*"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-100">
                        <template x-if="editing && form.cover_image">
                            <img :src="'/storage/' + form.cover_image" class="mt-2 h-12 rounded-lg object-cover" alt="">
                        </template>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_featured" id="is_featured" value="1"
                               x-model="form.is_featured" class="rounded border-slate-300 text-blue-600">
                        <label for="is_featured" class="text-xs font-medium text-slate-700">Featured (shown prominently)</label>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button type="submit"
                                class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                            <span x-text="editing ? 'Update Resource' : 'Save Resource'"></span>
                        </button>
                        <button type="button" @click="showForm = false; editing = null"
                                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 transition">Cancel</button>
                    </div>
                </form>
            </div>

            {{-- Resources list --}}
            @if($resources->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-sm text-slate-500">
                No resources yet. Click <strong>Add Resource</strong> to create one.
            </div>
            @else
            <div class="space-y-2">
                @foreach($resources as $res)
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 flex items-start gap-3 shadow-sm">
                    {{-- Cover --}}
                    @if($res->cover_image)
                    <img src="{{ asset('storage/' . $res->cover_image) }}"
                         class="flex-shrink-0 h-12 w-16 rounded-xl object-cover" alt="">
                    @else
                    <div class="flex-shrink-0 h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400">
                        @if($res->type === 'post')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        @elseif($res->type === 'link')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        @else
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        @endif
                    </div>
                    @endif

                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5 mb-0.5">
                            <span class="rounded-full bg-slate-100 text-slate-600 px-2 py-0.5 text-[10px] font-semibold uppercase">{{ $res->type }}</span>
                            @if($res->is_featured)<span class="rounded-full bg-amber-100 text-amber-700 px-2 py-0.5 text-[10px] font-semibold">Featured</span>@endif
                            @if(!$res->is_active)<span class="rounded-full bg-slate-100 text-slate-500 px-2 py-0.5 text-[10px] font-semibold">Hidden</span>@endif
                            @if($res->published_at && $res->published_at->isFuture())
                            <span class="rounded-full bg-blue-100 text-blue-600 px-2 py-0.5 text-[10px] font-semibold">Scheduled {{ \App\Support\EthiopianDate::smartFormat($res->published_at, false, '', 'h:i A', 'd M') }}</span>
                            @endif
                        </div>
                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $res->title }}</p>
                        @if($res->description)<p class="text-xs text-slate-500 line-clamp-1 mt-0.5">{{ $res->description }}</p>@endif
                    </div>

                    <div class="flex-shrink-0 flex items-center gap-1">
                        {{-- Toggle --}}
                        <form method="POST" action="{{ route('admin.landing.resources.toggle', $res) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 transition"
                                    title="{{ $res->is_active ? 'Hide' : 'Show' }}">
                                @if($res->is_active)
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                @else
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                @endif
                            </button>
                        </form>

                        {{-- Edit --}}
                        <button @click="
                                editing = {{ $res->id }};
                                form = {
                                    title: {{ json_encode($res->title) }},
                                    type: {{ json_encode($res->type) }},
                                    description: {{ json_encode($res->description) }},
                                    external_url: {{ json_encode($res->external_url) }},
                                    is_featured: {{ $res->is_featured ? 'true' : 'false' }},
                                    published_at: {{ $res->published_at ? json_encode($res->published_at->format('Y-m-d\TH:i')) : 'null' }},
                                    file_path: {{ json_encode($res->file_path) }},
                                    cover_image: {{ json_encode($res->cover_image) }}
                                };
                                showForm = true;
                                $nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }))
                            "
                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>

                        {{-- Delete --}}
                        <form method="POST" action="{{ route('admin.landing.resources.destroy', $res) }}"
                              onsubmit="return confirm('Delete this resource?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- =====================================================================
         TAB: FAQ
    ===================================================================== --}}
    <div x-show="tab === 'faqs'" x-cloak>
        <div x-data="{ showForm: false, editing: null, form: {} }" class="space-y-4">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">FAQ</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Questions and answers shown in the FAQ accordion on the landing page.</p>
                </div>
                <button @click="showForm = !showForm; editing = null; form = {}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add FAQ
                </button>
            </div>

            <div x-show="showForm" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                <h3 class="text-sm font-semibold text-slate-800 mb-4" x-text="editing ? 'Edit FAQ' : 'New FAQ'"></h3>

                <form :action="editing ? '/admin/landing/faqs/' + editing + '/update' : '{{ route('admin.landing.faqs.store') }}'"
                      method="POST" class="space-y-4">
                    @csrf
                    <input x-show="editing" type="hidden" name="_method" value="PUT">

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Question <span class="text-red-500">*</span></label>
                        <input type="text" name="question" x-model="form.question" required maxlength="500"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Answer <span class="text-red-500">*</span></label>
                        <textarea name="answer" x-model="form.answer" required rows="4" maxlength="2000"
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="editing ? 'Update FAQ' : 'Save FAQ'"></span>
                        </button>
                        <button type="button" @click="showForm = false; editing = null"
                                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 transition">Cancel</button>
                    </div>
                </form>
            </div>

            @if($faqs->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-sm text-slate-500">
                No FAQs yet. Click <strong>Add FAQ</strong> to create one.
            </div>
            @else
            <div class="space-y-2">
                @foreach($faqs as $faq)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                @if(!$faq->is_active)
                                <span class="rounded-full bg-slate-100 text-slate-500 px-2 py-0.5 text-[10px] font-semibold">Hidden</span>
                                @endif
                                <p class="text-sm font-semibold text-slate-900">{{ $faq->question }}</p>
                            </div>
                            <p class="text-xs text-slate-500 line-clamp-2">{{ $faq->answer }}</p>
                        </div>
                        <div class="flex-shrink-0 flex items-center gap-1">
                            <form method="POST" action="{{ route('admin.landing.faqs.toggle', $faq) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 transition"
                                        title="{{ $faq->is_active ? 'Hide' : 'Show' }}">
                                    @if($faq->is_active)
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    @else
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    @endif
                                </button>
                            </form>

                            <button @click="
                                    editing = {{ $faq->id }};
                                    form = {
                                        question: {{ json_encode($faq->question) }},
                                        answer: {{ json_encode($faq->answer) }}
                                    };
                                    showForm = true;
                                    $nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }))
                                "
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>

                            <form method="POST" action="{{ route('admin.landing.faqs.destroy', $faq) }}"
                                  onsubmit="return confirm('Delete this FAQ?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- =====================================================================
         TAB: FOOTER
    ===================================================================== --}}
    <div x-show="tab === 'footer'" x-cloak>
        <form method="POST" action="{{ route('admin.landing.footer.update') }}" class="space-y-6">
            @csrf @method('PUT')

            <div>
                <h2 class="text-base font-semibold text-slate-900">Footer Settings</h2>
                <p class="text-xs text-slate-500 mt-0.5">Contact info and social media links shown in the site footer.</p>
            </div>

            {{-- Description --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
                <h3 class="text-sm font-semibold text-slate-800">About blurb</h3>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Short description</label>
                    <textarea name="description" rows="3" maxlength="500"
                              placeholder="A brief description of the organization shown in the footer…"
                              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $footer['description'] ?? '' }}</textarea>
                </div>
            </div>

            {{-- Contact --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
                <h3 class="text-sm font-semibold text-slate-800">Contact information</h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Phone</label>
                        <input type="text" name="contact_phone" maxlength="60"
                               value="{{ $footer['contact_phone'] ?? '' }}"
                               placeholder="+251 11 123 4567"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" name="contact_email" maxlength="120"
                               value="{{ $footer['contact_email'] ?? '' }}"
                               placeholder="info@court.gov.et"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Physical address</label>
                        <input type="text" name="contact_address" maxlength="255"
                               value="{{ $footer['contact_address'] ?? '' }}"
                               placeholder="Addis Ababa, Ethiopia"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            {{-- Social links --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
                <h3 class="text-sm font-semibold text-slate-800">Social media links</h3>
                <p class="text-xs text-slate-500">Leave blank to hide the icon in the footer.</p>
                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach([
                        'social_facebook'  => ['Facebook',  'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z'],
                        'social_twitter'   => ['Twitter / X', 'M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z'],
                        'social_linkedin'  => ['LinkedIn',  'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z M2 6.5a2 2 0 114 0 2 2 0 01-4 0z'],
                        'social_youtube'   => ['YouTube',  'M22.54 6.42a2.78 2.78 0 00-1.94-1.96C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.4 19.54C5.12 20 12 20 12 20s6.88 0 8.6-.46a2.78 2.78 0 001.94-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58zM9.75 15.02V8.98L15.5 12l-5.75 3.02z'],
                        'social_telegram'  => ['Telegram',  'M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z'],
                        'social_instagram' => ['Instagram', 'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zM17.5 6.5h.01M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 01-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 017.8 2z'],
                    ] as $field => [$label, $icon])
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-slate-700 mb-1">{{ $label }}</label>
                            <input type="url" name="{{ $field }}" maxlength="300"
                                   value="{{ $footer[$field] ?? '' }}"
                                   placeholder="https://..."
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Footer
                </button>
            </div>
        </form>
    </div>

    {{-- =====================================================================
         TAB: SECTION SETTINGS
    ===================================================================== --}}
    <div x-show="tab === 'sections'" x-cloak>
        <form method="POST" action="{{ route('admin.landing.sections.update') }}" class="space-y-6">
            @csrf @method('PUT')

            <div>
                <h2 class="text-base font-semibold text-slate-900">Section Settings</h2>
                <p class="text-xs text-slate-500 mt-0.5">Control visibility and override titles for each landing page section. Leave a field blank to use the default translation.</p>
            </div>

            @php
            $sectionDefs = [
                'metrics'   => ['label' => 'Metrics / Statistics',   'hasTitleSub' => true,  'hasCta' => false],
                'process'   => ['label' => 'Process Timeline',        'hasTitleSub' => true,  'hasCta' => false],
                'services'  => ['label' => 'Services',                'hasTitleSub' => true,  'hasCta' => false],
                'cases'     => ['label' => 'Recent Cases',            'hasTitleSub' => false, 'hasCta' => false],
                'resources' => ['label' => 'Resources &amp; Posts',   'hasTitleSub' => true,  'hasCta' => false],
                'cta'       => ['label' => 'Call-to-Action Banner',   'hasTitleSub' => false, 'hasCta' => true],
            ];
            @endphp

            @foreach($sectionDefs as $key => $def)
            <div x-data="{ open: false }" class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between px-5 py-4 hover:bg-slate-50 transition text-left">
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer" @click.stop>
                            <input type="hidden"   name="{{ $key }}[visible]" value="0">
                            <input type="checkbox" name="{{ $key }}[visible]" value="1"
                                   {{ ($sections[$key]['visible'] ?? true) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-10 h-5 bg-slate-200 peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                        <span class="text-sm font-semibold text-slate-800">{!! $def['label'] !!}</span>
                    </div>
                    <svg class="h-4 w-4 text-slate-400 transition-transform" :class="{ 'rotate-180': open }"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" x-cloak class="px-5 pb-5 border-t border-slate-100 pt-4 space-y-4">
                    @if($def['hasTitleSub'])
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Section Title</label>
                            <input type="text" name="{{ $key }}[title]" maxlength="255"
                                   value="{{ $sections[$key]['title'] ?? '' }}"
                                   placeholder="Leave blank to use default"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Subtitle / Description</label>
                            <input type="text" name="{{ $key }}[subtitle]" maxlength="500"
                                   value="{{ $sections[$key]['subtitle'] ?? '' }}"
                                   placeholder="Leave blank to use default"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    @endif

                    @if($def['hasCta'])
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-700 mb-1">Banner Title</label>
                            <input type="text" name="cta[title]" maxlength="255"
                                   value="{{ $sections['cta']['title'] ?? '' }}"
                                   placeholder="Ready to engage with the tribunal?"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-700 mb-1">Banner Description</label>
                            <textarea name="cta[description]" rows="2" maxlength="500"
                                      placeholder="File a case, track an existing matter…"
                                      class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $sections['cta']['description'] ?? '' }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Primary button label</label>
                            <input type="text" name="cta[primary_label]" maxlength="120"
                                   value="{{ $sections['cta']['primary_label'] ?? '' }}"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Primary button URL</label>
                            <input type="text" name="cta[primary_href]" maxlength="500"
                                   value="{{ $sections['cta']['primary_href'] ?? '' }}"
                                   placeholder="/applicant/register"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Secondary button label</label>
                            <input type="text" name="cta[secondary_label]" maxlength="120"
                                   value="{{ $sections['cta']['secondary_label'] ?? '' }}"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Secondary button URL</label>
                            <input type="text" name="cta[secondary_href]" maxlength="500"
                                   value="{{ $sections['cta']['secondary_href'] ?? '' }}"
                                   placeholder="/applicant/login"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach

            <div class="flex justify-end pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Settings
                </button>
            </div>
        </form>
    </div>

</div>
</x-admin-layout>
