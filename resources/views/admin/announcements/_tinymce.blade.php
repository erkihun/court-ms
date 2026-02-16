@once
    @push('scripts')
        {{-- Load LOCAL TinyMCE --}}
        <script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
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

                    toolbar_mode: 'wrap',
                    toolbar_sticky: true,

                    plugins: 'lists link table code image advlist charmap fullscreen',
                    toolbar: [
                        'undo redo |  fontfamily fontsize | bold italic underline strikethrough removeformat',
                        '| forecolor backcolor | alignleft aligncenter alignright alignjustify',
                        '| numlist bullist outdent indent  | fullscreen code'
                    ].join(' '),

                    forced_root_block: 'p',
                    forced_root_block_attrs: {
                        style: 'text-align: justify;'
                    },

                    content_style: `
                        body, p, div, li, td, th, blockquote { text-align: justify; text-justify: inter-word; }
                        table{width:100%;border-collapse:collapse}
                        td,th{border:1px solid #ddd;padding:4px}
                        body{font-size:14px;line-height:1.5}
                    `,

                    paste_postprocess(plugin, args) {
                        const blocks = args.node.querySelectorAll('p,div,li,td,th,blockquote');
                        blocks.forEach(el => {
                            el.style.textAlign = 'justify';
                        });
                    },

                    resize: false,
                    statusbar: true,

                    setup(editor) {
                        editor.on('init', () => {
                            editor.execCommand('JustifyFull');
                        });
                    }
                };

                tinymce.init({
                    ...common,
                    selector: '#announcement-content',
                    height: 520,
                    min_height: 520,
                    max_height: 520
                });
            })();
        </script>
    @endpush
@endonce
