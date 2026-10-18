import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import { viteStaticCopy } from 'vite-plugin-static-copy';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.tsx',
            ],
            refresh: true,
        }),
        react(),
        tailwindcss(),
        // Self-hosted TinyMCE: node_modules/tinymce -> public/build/vendor/tinymce (the version in package.json is the one served)
        viteStaticCopy({
            targets: [{ src: 'node_modules/tinymce', dest: 'vendor' }],
        }),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    build: {
        assetsInlineLimit: 4096,
        cssCodeSplit: true,
        sourcemap: false,
        rollupOptions: {
            output: {
                // Large libraries in their own chunks so app changes don't bust their cache.
                manualChunks(id) {
                    if (!id.includes('node_modules')) return undefined;
                    if (/node_modules\/(react|react-dom|scheduler|@inertiajs|axios|ziggy-js|qs|side-channel|es-toolkit|nprogress)\//.test(id)) return 'vendor-react';
                    if (/node_modules\/(chart\.js|react-chartjs-2|@kurkle)\//.test(id)) return 'vendor-chart';
                    return undefined;
                },
            },
        },
    },
});
