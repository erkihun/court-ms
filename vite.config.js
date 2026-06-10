import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import { viteStaticCopy } from 'vite-plugin-static-copy'

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
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

  build: {
    // Inline small assets (< 4 KB) directly into CSS/JS
    assetsInlineLimit: 4096,

    rollupOptions: {
      output: {
        // Split large vendor libraries into separate chunks so browsers
        // cache them independently from app code changes
        manualChunks: {
          'vendor-chart':  ['chart.js'],
          'vendor-alpine': ['alpinejs'],
        },
      },
    },

    cssCodeSplit: true,
    sourcemap: false,
  },
})
