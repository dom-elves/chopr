import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            // not sure why but process.env.VITE_DEV_HOST works on mac
            // but doesn't on pc
            // so use null coalescing to cover both

            // old
            // host: process.env.VITE_DEV_HOST ?? '192.168.0.20',

            // current?
            host: process.env.VITE_HOST ?? '127.0.0.1',
            // host: '192.168.0.20',
            protocol: 'ws',
            port: 5173,
        },
    }
});
