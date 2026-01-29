<x-admin-layout title="{{ __('about.create_heading') }}">
    @section('page_header', __('about.create_heading'))

    <div class="p-6 bg-white rounded-xl border border-gray-200">
        <h1 class="text-2xl font-semibold text-gray-900 mb-4">{{ __('about.create_heading') }}</h1>
        <form method="POST" action="{{ route('about.store') }}" class="space-y-6">
            @include('admin.about._form', ['about' => null])

            <div class="flex items-center gap-3">
                <a href="{{ route('about.index') }}" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 text-sm">
                    {{ __('about.form_cancel') }}
                </a>
                <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700">
                    {{ __('about.form_save') }}
                </button>
            </div>
        </form>
    </div>
    @push('scripts')
        <script src="{{ asset('build/vendor/tinymce/tinymce.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof tinymce === 'undefined') return;
                const TINY_BASE = "{{ asset('build/vendor/tinymce') }}";
                tinymce.init({
                    selector: '#about-body',
                    license_key: 'gpl',
                    height: 520,
                    menubar: 'file edit view insert format tools table help',
                    branding: false,
                    plugins: 'advlist autolink lists link table code preview searchreplace wordcount charmap insertdatetime fullscreen autoresize',
                    toolbar: [
                        'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist outdent indent',
                        'link table | charmap insertdatetime | searchreplace | preview code fullscreen'
                    ],
                    content_css: `${TINY_BASE}/skins/content/default/content.min.css`,
                    skin_url: `${TINY_BASE}/skins/ui/oxide`,
                });
            });
        </script>
    @endpush
</x-admin-layout>
