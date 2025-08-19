import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';
import { configDefaults } from 'vitest/config';
import { VitePWA } from 'vite-plugin-pwa';
import path from 'path';

export default defineConfig(({ mode }) => {
    const isProduction = mode === 'production';

    return {
        plugins: [
            laravel({
                input: ['resources/js/app.ts', 'resources/css/app.css'],
                ssr: 'resources/js/ssr.ts',
                refresh: true,
            }),
            tailwindcss(),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        VitePWA({
            registerType: 'autoUpdate',
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,webp}'],
                // Tambahkan strategi caching untuk single bundle
                runtimeCaching: [
                    {
                        urlPattern: /\/build\/assets\/app\..+\.js$/,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'app-js-cache',
                            expiration: {
                                maxEntries: 5,
                                maxAgeSeconds: 60 * 60 * 24 * 30 // 30 hari
                            }
                        }
                    },
                    {
                        urlPattern: /\/build\/assets\/app\..+\.css$/,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'app-css-cache',
                            expiration: {
                                maxEntries: 5,
                                maxAgeSeconds: 60 * 60 * 24 * 30 // 30 hari
                            }
                        }
                    },
                    {
                        urlPattern: /^https:\/\/fonts\.googleapis\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'google-fonts-cache',
                            expiration: {
                                maxEntries: 10,
                                maxAgeSeconds: 60 * 60 * 24 * 365 // <== 365 days
                            }
                        }
                    },
                    {
                        urlPattern: /^https:\/\/fonts\.gstatic\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'gstatic-fonts-cache',
                            expiration: {
                                maxEntries: 10,
                                maxAgeSeconds: 60 * 60 * 24 * 365 // <== 365 days
                            }
                        }
                    },
                    {
                        urlPattern: /\/api\/.*/i,
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'api-cache',
                            expiration: {
                                maxEntries: 100,
                                maxAgeSeconds: 60 * 60 * 24 // <== 24 hours
                            }
                        }
                    }
                ]
            },
            includeAssets: ['favicon.ico', 'apple-touch-icon.png', 'safari-pinned-tab.svg'],
            manifest: {
                name: 'Cashier E-Commerce',
                short_name: 'Cashier App',
                description: 'Point of Sale system for retail businesses',
                theme_color: '#ffffff',
                background_color: '#ffffff',
                display: 'standalone',
                orientation: 'portrait',
                scope: '/',
                start_url: '/',
                icons: [
                    {
                        src: '/android-chrome-192x192.png',
                        sizes: '192x192',
                        type: 'image/png'
                    },
                    {
                        src: '/android-chrome-512x512.png',
                        sizes: '512x512',
                        type: 'image/png'
                    },
                    {
                        src: '/android-chrome-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any maskable'
                    }
                ]
            }
        })
    ],
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'resources/js'),
            },
        },
        build: isProduction ? {
            rollupOptions: {
                output: {
                    manualChunks: () => 'app', // Paksa semua JS menjadi satu chunk hanya di production
                    assetFileNames: (assetInfo) => {
                        // Gabungkan semua CSS menjadi satu file
                        if (assetInfo.name?.endsWith('.css')) {
                            return 'assets/app.[hash].css';
                        }
                        return 'assets/[name].[hash].[ext]';
                    },
                    chunkFileNames: 'assets/app.[hash].js',
                    entryFileNames: 'assets/app.[hash].js'
                }
            },
            cssCodeSplit: true, // Biarkan CSS terpisah untuk PWA
            assetsInlineLimit: 0, // Jangan inline assets kecil
        } : {
            // Development: gunakan code splitting normal
            rollupOptions: {},
        },
        test: {
            globals: true,
            environment: 'jsdom',
            setupFiles: ['resources/js/tests/setup.ts'],
            exclude: [...configDefaults.exclude, 'tests/**'],
        },
    };
});
