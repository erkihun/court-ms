import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import { viteStaticCopy } from 'vite-plugin-static-copy'

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/admin-chat.js',
      ],
      refresh: true,
    }),

    // Copies node_modules/tinymce -> public/vendor/tinymce
    viteStaticCopy({
      targets: [
        { src: 'node_modules/tinymce', dest: 'vendor' },
      ],
    }),
  ],
  // server: { port: 5174 }, // optional
})
