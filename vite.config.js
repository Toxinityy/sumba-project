import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    // In dev the stylesheet is served from the Vite origin, so fonts.css's
    // absolute /fonts/... URLs resolve there, and the Laravel plugin disables
    // Vite's public dir: every face 404'd and fell back to Georgia/system sans.
    // Serving public/ in dev fixes that; copyPublicDir stops the build copying
    // public/ into its own public/build subfolder.
    publicDir: 'public',
    build: { copyPublicDir: false },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
