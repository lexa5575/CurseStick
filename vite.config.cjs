// Файл больше не используется, но оставлен для обратной совместимости
const { defineConfig } = require('vite');
const laravel = require('laravel-vite-plugin');
const react = require('@vitejs/plugin-react');
const tailwindcss = require('@tailwindcss/vite');

module.exports = defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true
        }),
        react(),
        tailwindcss()
    ],
    server: {
        hmr: {
            host: 'localhost'
        }
    },
    build: {
        sourcemap: true
    },
    optimizeDeps: {
        include: ['@inertiajs/inertia', '@inertiajs/inertia-react', 'react', 'react-dom']
    }
});
