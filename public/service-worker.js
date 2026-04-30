const CACHE_NAME = 'epic-hub-pwa-v1';
const OFFLINE_URL = '/offline';
const PRECACHE_URLS = [
    '/',
    OFFLINE_URL,
    '/manifest.webmanifest',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon-maskable-512.png',
    '/icons/apple-touch-icon.png',
];
const STATIC_CACHE_PREFIXES = ['/build/', '/icons/'];
const SENSITIVE_PATH_PREFIXES = [
    '/admin',
    '/api',
    '/checkout',
    '/orders',
    '/payments',
    '/produk-saya',
    '/kelas-saya',
    '/event-saya',
    '/dashboard',
    '/epi-channel',
    '/login',
    '/register',
    '/forgot-password',
    '/reset-password',
    '/two-factor',
    '/verify-email',
    '/settings',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_URLS))
    );

    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) =>
            Promise.all(
                cacheNames
                    .filter((cacheName) => cacheName !== CACHE_NAME)
                    .map((cacheName) => caches.delete(cacheName))
            )
        )
    );

    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(handleNavigationRequest(request));
        return;
    }

    if (isSensitivePath(url.pathname)) {
        return;
    }

    if (shouldCacheStaticAsset(url.pathname)) {
        event.respondWith(cacheFirst(request));
    }
});

function shouldCacheStaticAsset(pathname) {
    return pathname === '/manifest.webmanifest'
        || STATIC_CACHE_PREFIXES.some((prefix) => pathname.startsWith(prefix));
}

function isSensitivePath(pathname) {
    return SENSITIVE_PATH_PREFIXES.some((prefix) => pathname.startsWith(prefix));
}

async function handleNavigationRequest(request) {
    try {
        return await fetch(request);
    } catch (error) {
        const offlineResponse = await caches.match(OFFLINE_URL);

        if (offlineResponse) {
            return offlineResponse;
        }

        throw error;
    }
}

async function cacheFirst(request) {
    const cachedResponse = await caches.match(request);

    if (cachedResponse) {
        return cachedResponse;
    }

    const networkResponse = await fetch(request);

    if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
        return networkResponse;
    }

    const cache = await caches.open(CACHE_NAME);
    cache.put(request, networkResponse.clone());

    return networkResponse;
}
