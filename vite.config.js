import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
    },
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
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
    build: {
        // Pisah vendor besar → chunk stabil ter-cache browser (hash tidak berubah
        // saat source sendiri berubah). Rolldown: output.advancedChunks.
        rolldownOptions: {
            output: {
                advancedChunks: {
                    groups: [
                        { name: 'vue-vendor', test: /node_modules\/(vue|@vue|vue-i18n|@intlify|@inertiajs|ziggy)/ },
                        { name: 'charts-vendor', test: /node_modules\/(apexcharts|vue3-apexcharts|sweetalert2|html5-qrcode|qrcode)/ },
                    ],
                },
            },
        },
    },
});