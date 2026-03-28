import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/offline-manager.js',
                'resources/js/offline-integration.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
        VitePWA({
            registerType: 'autoUpdate',
            injectRegister: 'auto',
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg}'],
                // Configuración para que las rutas de Laravel funcionen offline
                navigateFallback: '/',
                navigateFallbackDenylist: [/^\/api/],
            },
            manifest: {
                name: 'Bobaguette POS',
                short_name: 'Bobaguette',
                description: 'Punto de Venta Bobaguette',
                theme_color: '#004225',
                background_color: '#EAE0CC',
                display: 'standalone',
                orientation: 'portrait',
                icons: [
                    {
                        src: '/img/boba-chan.png',
                        sizes: '192x192',
                        type: 'image/png'
                    },
                    {
                        src: '/img/boba-chan.png',
                        sizes: '512x512',
                        type: 'image/png'
                    }
                ]
            }
        })
    ],
});