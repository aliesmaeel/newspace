import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
        // CSS url(/assets/...) resolves against the Vite origin in dev;
        // proxy those requests to Laravel so public/assets images load.
        proxy: {
            '/assets': {
                target: 'http://127.0.0.1:8000',
            },
            '/storage': {
                target: 'http://127.0.0.1:8000',
            },
        },
    },
});
