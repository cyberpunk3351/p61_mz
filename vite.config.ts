import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const devOrigin = env.VITE_DEV_ORIGIN || 'http://localhost:3000';
    const devHost = env.VITE_DEV_HOST || 'localhost';

    return {
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
            port: parseInt(env.VITE_PORT) || 5183,
            cors: {
                origin: true,
                credentials: true,
            },
            strictPort: true,
            watch: {
                usePolling: true,
            },
            hmr: {
                host: devHost,
                port: parseInt(env.VITE_PORT) || 5183,
            },
        },
    };
});
