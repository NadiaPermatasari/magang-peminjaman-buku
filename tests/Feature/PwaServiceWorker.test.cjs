const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { resolve } = require('node:path');
const { runInNewContext } = require('node:vm');

const source = readFileSync(resolve(__dirname, '../../public/sw.js'), 'utf8');

function worker({ scope = 'https://arsip.test/', offline = false, status = 200 } = {}) {
    const listeners = {};
    const stored = new Map();
    const deleted = [];
    const fallback = new Response('Halaman offline', { headers: { 'Content-Type': 'text/html' } });
    const cache = {
        addAll: async (requests) => requests.forEach((request) => stored.set(request.url, request)),
        match: async (url) => url === `${scope}offline` ? fallback : undefined,
    };
    const prefix = `peminjaman-buku-pwa:${scope}:`;
    const context = {
        URL, Request, Response,
        self: {
            registration: { scope },
            clients: { claim: async () => {} },
            addEventListener: (name, callback) => { listeners[name] = callback; },
        },
        caches: {
            open: async () => cache,
            keys: async () => [`${prefix}v0`, `${prefix}v1`, 'another-app-cache'],
            delete: async (name) => { deleted.push(name); },
        },
        fetch: async () => {
            if (offline) throw new TypeError('Koneksi terputus');
            return new Response('Respons server', { status });
        },
    };

    runInNewContext(source, context);

    return { listeners, stored, deleted, prefix, fallback };
}

async function navigate(instance, url, { method = 'GET', mode = 'navigate' } = {}) {
    let result;
    instance.listeners.fetch({
        request: { url, method, mode },
        respondWith: (promise) => { result = promise; },
    });
    return result;
}

test('installation only caches the public offline page and bundled icons without credentials', async () => {
    const instance = worker();
    await new Promise((resolve) => instance.listeners.install({ waitUntil: resolve }));

    assert.deepEqual([...instance.stored.keys()].sort(), [
        'https://arsip.test/offline',
        'https://arsip.test/pwa/icons/apple-touch-icon.png',
        'https://arsip.test/pwa/icons/icon-192.png',
        'https://arsip.test/pwa/icons/icon-512.png',
    ]);
    for (const request of instance.stored.values()) {
        assert.equal(request.credentials, 'omit');
    }
});

test('online private pages and server errors pass through without being cached', async () => {
    for (const status of [200, 403, 404, 500]) {
        const instance = worker({ status });
        const response = await navigate(instance, 'https://arsip.test/members');
        assert.equal(response.status, status);
        assert.equal(await response.text(), 'Respons server');
        assert.equal(instance.stored.size, 0);
    }
});

test('failed navigation serves the generic offline page without caching the requested URL', async () => {
    const instance = worker({ offline: true });
    const response = await navigate(instance, 'https://arsip.test/loans/private-id');
    assert.equal(response, instance.fallback);
    assert.equal(instance.stored.size, 0);
});

test('form submissions, uploads, APIs, storage files and external requests are not intercepted', async () => {
    const instance = worker({ offline: true });
    for (const [url, options] of [
        ['https://arsip.test/logout', { method: 'POST' }],
        ['https://arsip.test/returns', { method: 'POST' }],
        ['https://arsip.test/loans', { method: 'POST' }],
        ['https://arsip.test/members', { mode: 'cors' }],
        ['https://arsip.test/storage/loan-proofs/private.jpg', { mode: 'no-cors' }],
        ['https://another.test/', {}],
    ]) {
        assert.equal(await navigate(instance, url, options), undefined);
    }
});

test('activation deletes only older caches belonging to this app and scope', async () => {
    const instance = worker();
    await new Promise((resolve) => instance.listeners.activate({ waitUntil: resolve }));
    assert.deepEqual(instance.deleted, [`${instance.prefix}v0`]);
});

test('offline navigation also works when the app is deployed in a subdirectory', async () => {
    const instance = worker({ scope: 'https://arsip.test/layanan/', offline: true });
    await new Promise((resolve) => instance.listeners.install({ waitUntil: resolve }));
    assert.ok(instance.stored.has('https://arsip.test/layanan/offline'));
    assert.equal(await navigate(instance, 'https://arsip.test/layanan/dashboard'), instance.fallback);
    assert.equal(await navigate(instance, 'https://arsip.test/another-app/'), undefined);
});
