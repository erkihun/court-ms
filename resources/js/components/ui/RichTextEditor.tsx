import { Editor } from '@tinymce/tinymce-react';

interface RichTextEditorProps {
    id?: string;
    value: string;
    onChange: (html: string) => void;
    height?: number;
    disabled?: boolean;
}

/** Self-hosted TinyMCE (public/vendor/tinymce). Output is sanitised again on the server. */
export function RichTextEditor({ id, value, onChange, height = 420, disabled = false }: RichTextEditorProps) {
    // The skin is chosen when the editor mounts; a later theme switch applies on the next page.
    const dark = document.documentElement.classList.contains('dark');

    return (
        <Editor
            id={id}
            tinymceScriptSrc="/vendor/tinymce/tinymce.min.js"
            licenseKey="gpl"
            value={value}
            disabled={disabled}
            onEditorChange={(html) => onChange(html)}
            init={{
                height,
                min_height: height,
                max_height: height,
                resize: false,
                branding: false,
                promotion: false,
                menubar: true,
                toolbar_mode: 'wrap',
                toolbar_sticky: true,
                plugins: 'lists link table code image advlist charmap fullscreen',
                toolbar:
                    'undo redo | fontfamily fontsize | bold italic underline strikethrough removeformat | forecolor backcolor | alignleft aligncenter alignright alignjustify | numlist bullist outdent indent | fullscreen code',
                // New paragraphs are justified, as legal drafts expect.
                forced_root_block: 'p',
                forced_root_block_attrs: { style: 'text-align: justify;' },
                skin: dark ? 'oxide-dark' : 'oxide',
                content_css: dark ? 'dark' : 'default',
                content_style:
                    'body, p, div, li, td, th, blockquote { text-align: justify; text-justify: inter-word; } table{width:100%;border-collapse:collapse} td,th{border:1px solid #ddd;padding:4px} body{font-size:14px;line-height:1.5}',
                paste_postprocess: (_plugin: unknown, args: { node: HTMLElement }) => {
                    args.node.querySelectorAll<HTMLElement>('p,div,li,td,th,blockquote').forEach((element) => {
                        element.style.textAlign = 'justify';
                    });
                },
            }}
        />
    );
}
