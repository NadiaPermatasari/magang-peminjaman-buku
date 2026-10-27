// Bump this version when the offline page or bundled icons change.
const CACHE_PREFIX = `peminjaman-buku-pwa:${self.registration.scope}:`;
const CACHE_NAME = `${CACHE_PREFIX}v1`;
const OFFLINE_URL = new URL('offline', self.registration.scope).href;
const PUBLIC_FILES = [
    OFFLINE_URL,
    new URL('pwa/icons/icon-192.png', self.registration.scope).href,
    new URL('pwa/icons/icon-512.png', self.registration.scope).href,
    new URL('pwa/icons/apple-touch-icon.png', self.registration.scope).href,
];

self.addEventListener('install', (event) => {
    // Omit credentials even if installation happens while a user is logged in.
    event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll(
        PUBLIC_FILES.map((url) => new Request(url, { cache: 'reload', credentials: 'omit' })),
    )));
    // Let the normal lifecycle activate updates after existing tabs close.
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const names = await caches.keys();
        await Promise.all(names
            .filter((name) => name.startsWith(CACHE_PREFIX) && name !== CACHE_NAME)
            .map((name) => caches.delete(name)));
        await self.clients.claim();
    })());
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Never intercept form submissions, uploads, API calls, or external URLs.
    if (request.method !== 'GET' || request.mode !== 'navigate'
        || !request.url.startsWith(self.registration.scope)) return;

    event.respondWith((async () => {
        try {
            // Authenticated HTML, redirects, 403/404 responses and downloads
            // always come from the server and are never written to Cache Storage.
            return await fetch(request);
        } catch (_) {
            const cache = await caches.open(CACHE_NAME);
            return (await cache.match(OFFLINE_URL)) || Response.error();
        }
    })());
});
