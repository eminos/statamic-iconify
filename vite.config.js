import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwind from '@tailwindcss/vite';
import statamic from '@statamic/cms/vite-plugin'; 
 
export default defineConfig({
    plugins: [
        statamic(),
        tailwind(),
        laravel({
            input: [
                'resources/js/iconify-fieldtype.js',
                'resources/css/iconify-fieldtype.css',
            ],
            hotFile: 'dist/vite.hot',
            publicDirectory: 'dist',
        })
    ],
});