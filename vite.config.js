import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/admin.css',
                'resources/css/public.css',
                'resources/css/filament-sidebar.css',
                'resources/js/app.js',
                'resources/js/public.js',
                'resources/js/admin-dashboard.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
