import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import {svelte} from '@sveltejs/vite-plugin-svelte';
import commonjs from 'vite-plugin-commonjs';
import postcss from 'postcss';
import tailwindcss from 'tailwindcss';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        postcss({
            plugins: [
                tailwindcss(resolve('./tailwind.config.js')),
                // autres plugins PostCSS (autoprefixer, etc.)
            ],
        }),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
        svelte(),
        commonjs()
    ],
    resolve: {
        alias: {
            '@': resolve(__dirname, '/resources/js'),
        },
    },
    server: {
        host: 'localhost'
    }
});
