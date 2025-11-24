import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
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
        cors: {
            origin: 'http://192.168.31.166:8234',
            credentials: true,
        },
        strictPort: true,
        watch: {
            usePolling: true,
        },
        headers: {
            'Access-Control-Allow-Origin': 'http://192.168.31.166:8234',
        },
        hmr: {
            host: '192.168.31.166'
        },
    },
});
