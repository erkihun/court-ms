{{-- resources/views/admin/letters/_tinymce-script.blade.php --}}
<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tinyBase = "{{ asset('vendor/tinymce') }}";
        tinymce.init({
            base_url: tinyBase,
            suffix: '.min',
            selector: '#letter-body-editor',
            license_key: 'gpl',
            branding: false,
            promotion: false,
            menubar: false,
            toolbar_mode: 'wrap',
            plugins: 'lists link table code fullscreen preview',
            toolbar: [
                'undo redo | bold italic underline strikethrough | blockquote',
                '| alignleft aligncenter alignright alignjustify',
                '| bullist numlist outdent indent',
                '| link table | code fullscreen preview'
            ].join(' '),
            forced_root_block: 'p',
            content_style: `
                body { font-family: 'Times New Roman', serif; font-size: 14px; line-height: 1.6; text-align: justify; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #d1d5db; padding: 6px; }
            `,
            statusbar: true,
            resize: false,
            height: 420,
            setup(editor) {
                editor.on('init', () => {
                    editor.execCommand('JustifyFull');
                });
            }
        });
    });
</script>
