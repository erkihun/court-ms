import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import tailwindcss3 from 'tailwindcss-v3'
import autoprefixer from 'autoprefixer'

/*
 * The legacy Blade screens (Tailwind 3, Alpine, Bootstrap/AdminLTE, Chart.js)
 * while they move to the EUISIS stack (vite.config.js). Built into
 * public/build-legacy; the Blade layouts load it with
 * @vite([...], 'build-legacy'). Removed with the last Blade screen.
 */
export default defineConfig({
  css: {
    // Tailwind 3 through PostCSS, under its npm alias so Tailwind 4 can serve the new build.
    postcss: {
      plugins: [tailwindcss3({ config: './tailwind.config.js' }), autoprefixer()],
    },
  },
  plugins: [
    laravel({
      input: [
        'resources/legacy/css/app.css',
        'resources/legacy/js/app.js',
        'resources/legacy/js/dashboard.js',
      ],
      buildDirectory: 'build-legacy',
      hotFile: 'public/hot-legacy',
      refresh: true,
    }),
    // TinyMCE for the Blade editors is served from the committed public/vendor/tinymce.
  ],

  build: {
    // Inline small assets (< 4 KB) directly into CSS/JS
    assetsInlineLimit: 4096,

    rollupOptions: {
      output: {
        // Split large vendor libraries into separate chunks so browsers
        // cache them independently from app code changes
        // (Vite 8 takes only the function form.)
        manualChunks(id) {
          if (id.includes('/node_modules/chart.js/')) return 'vendor-chart'
          if (id.includes('/node_modules/alpinejs/')) return 'vendor-alpine'
          return undefined
        },
      },
    },

    cssCodeSplit: true,
    sourcemap: false,
  },
})
