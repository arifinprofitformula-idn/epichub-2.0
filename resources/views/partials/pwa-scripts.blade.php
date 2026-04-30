<script>
window.__epicHubDeferredInstallPrompt = window.__epicHubDeferredInstallPrompt || null;

window.addEventListener('beforeinstallprompt', function (event) {
    event.preventDefault();
    window.__epicHubDeferredInstallPrompt = event;
    window.dispatchEvent(new CustomEvent('epic-hub:install-ready'));
});

window.addEventListener('appinstalled', function () {
    window.__epicHubDeferredInstallPrompt = null;
    window.dispatchEvent(new CustomEvent('epic-hub:installed'));
});

if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/service-worker.js').catch(function (error) {
            console.warn('Service worker registration failed:', error);
        });
    });
}
</script>
