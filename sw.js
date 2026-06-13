/**
 * MAB Shop - Service Worker for PWA offline support
 */
const CACHE_NAME = 'mab-shop-v1';
const ASSETS = [
    '/Shop/',
    '/Shop/index.php',
    '/Shop/assets/css/style.css',
    '/Shop/assets/js/main.js',
    '/Shop/assets/images/placeholder.svg'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request).then((cached) => {
            return cached || fetch(event.request).catch(() => caches.match('/Shop/index.php'));
        })
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
        )
    );
});
