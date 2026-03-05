import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';  // ← AÑADIR

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        vue(),
    ],
    resolve: {              // ← AÑADIR ESTO
        alias: {
            '@': resolve(__dirname, 'frontend/src'),
        },
    },
    build: {
        outDir: 'public/build',
	manifest: 'manifest.json',
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
