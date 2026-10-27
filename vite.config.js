import path from 'node:path';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

/*
 * The EUISIS stack (React, Inertia, TypeScript, Tailwind 4), as EUISIS builds
 * it (EUISIS vite.config.js). The legacy Blade screens build separately with
 * vite.legacy.config.js until the last of them moves here.
 */
const devServerHost = process.env.VITE_DEV_SERVER_HOST ?? '127.0.0.1';
const devServerPort = Number(process.env.VITE_DEV_SERVER_PORT ?? 5173);

export default defineConfig({
    server: {
        host: devServerHost,
        port: devServerPort,
        strictPort: true,
        hmr: { host: devServerHost, port: devServerPort, clientPort: devServerPort },
    },
    css: {
        // Tailwind 4 runs as a Vite plugin; no PostCSS step (the legacy build has its own).
        postcss: { plugins: [] },
    },
    resolve: {
        alias: {
            // Ziggy's route() helper ships with the Composer package; no npm copy to keep in sync.
            'ziggy-js': path.resolve('vendor/tightenco/ziggy'),
        },
    },
    plugins: [
        tailwindcss(),
        laravel({
            input: 'resources/js/app.tsx',
            refresh: true,
        }),
        react(),
    ],
    build: {
        chunkSizeWarningLimit: 600,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    const file = id.replace(/\\/g, '/');
                    if (file.endsWith('/resources/js/ziggy.js')) {
                        return 'ziggy';
                    }
                    // The rich-text editor and its CommonJS core in one lazily loaded chunk (as in EUISIS).
                    if (((file.includes('/node_modules/tinymce/') || file.includes('/node_modules/@tinymce/')) && !file.includes('/skins/'))
                        || file.endsWith('/resources/js/richtext/tinymceGlobal.ts')) {
                        return 'rich-text-editor';
                    }
                    // React context providers in one shared chunk, so no page gets its own copy.
                    if (id.includes('ConfirmProvider') || id.includes('ToastProvider')) {
                        return 'providers';
                    }
                },
            },
        },
    },
});
