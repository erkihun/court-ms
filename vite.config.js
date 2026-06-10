import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import { viteStaticCopy } from 'vite-plugin-static-copy'
import viteCompression from 'vite-plugin-compression'

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

    // Generate .gz alongside every asset — Apache serves pre-compressed files
    // automatically when mod_deflate sees them (no CPU cost per request)
    viteCompression({
      algorithm: 'gzip',
      ext: '.gz',
      threshold: 1024, // only compress files > 1 KB
      deleteOriginFile: false,
    }),

    // Also generate .br (Brotli) for hosts that support it
    viteCompression({
      algorithm: 'brotliCompress',
      ext: '.br',
      threshold: 1024,
      deleteOriginFile: false,
    }),
  ],

  build: {
    // Raise the inline-asset threshold so small SVGs/images stay inlined
    assetsInlineLimit: 4096,

    rollupOptions: {
      output: {
        // Split large vendor libraries into separate chunks so browsers can
        // cache them independently from app code changes
        manualChunks: {
          'vendor-chart': ['chart.js'],
          'vendor-alpine': ['alpinejs'],
        },
      },
    },

    // Emit CSS as a separate file (already default, made explicit)
    cssCodeSplit: true,

    // Source maps only for development — never ship them to production
    sourcemap: false,
  },
})
