const manifest = document.querySelector('link[rel="manifest"]');

if (manifest && window.isSecureContext && 'serviceWorker' in navigator) {
    // Resolve from the manifest URL so ASSET_URL/CDNs and subdirectories do
    // not change the service worker's origin or scope.
    const scope = new URL('./', manifest.href);
    const register = () => {
        navigator.serviceWorker.register(new URL('sw.js', scope), {
            scope: scope.href,
            updateViaCache: 'none',
        }).catch((error) => {
            console.warn('PWA belum dapat diaktifkan.', error);
        });
    };

    if (document.readyState === 'complete') {
        register();
    } else {
        window.addEventListener('load', register, { once: true });
    }

    const install = document.getElementById('pwa-install');
    const button = document.getElementById('pwa-install-button');
    const dismiss = document.getElementById('pwa-install-dismiss');
    const help = document.getElementById('pwa-install-help');
    const standalone = window.matchMedia('(display-mode: standalone)');
    const isInstalled = () => standalone.matches || navigator.standalone === true;
    const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent)
        || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    let deferredPrompt = null;
    let dismissed = false;

    try {
        dismissed = sessionStorage.getItem('pwa-install-dismissed') === 'true';
    } catch (_) {
        // Installation still works when browser storage is unavailable.
    }

    const updateInstallButton = () => {
        install?.classList.toggle('hidden', isInstalled() || dismissed || (!deferredPrompt && !isIos));
    };

    window.addEventListener('beforeinstallprompt', (event) => {
        if (!button || isInstalled()) return;

        event.preventDefault();
        deferredPrompt = event;
        updateInstallButton();
    });

    window.addEventListener('appinstalled', () => {
        deferredPrompt = null;
        install?.classList.add('hidden');
        if (help?.open) help.close();
    });

    standalone.addEventListener('change', updateInstallButton);

    button?.addEventListener('click', async () => {
        if (!deferredPrompt) {
            if (isIos) help?.showModal();
            return;
        }

        const prompt = deferredPrompt;
        deferredPrompt = null;
        updateInstallButton();

        try {
            await prompt.prompt();
            await prompt.userChoice;
        } catch (error) {
            console.warn('Pemasangan aplikasi belum berhasil.', error);
        }
    });

    dismiss?.addEventListener('click', () => {
        dismissed = true;
        updateInstallButton();

        try {
            sessionStorage.setItem('pwa-install-dismissed', 'true');
        } catch (_) {
            // Dismissal applies to this page when storage is unavailable.
        }
    });

    updateInstallButton();
}
