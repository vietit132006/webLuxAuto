import { defineConfig } from 'vite';
import { readdirSync } from 'node:fs';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

const cssFiles = readdirSync('resources/css')
    .filter((file) => file.endsWith('.css'))
    .map((file) => `resources/css/${file}`);

export default defineConfig({
    plugins: [
        laravel({
            input: [...cssFiles, 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
