// pwa.js
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('service-worker.js')
        .then(reg => {
            console.log('Service worker registered successfully', reg);

            reg.addEventListener('updatefound', () => {
                // A wild service worker has appeared in reg.installing!
                const newWorker = reg.installing;

                newWorker.addEventListener('statechange', () => {
                    // Has network.state changed?
                    switch (newWorker.state) {
                        case 'installed':
                            if (navigator.serviceWorker.controller) {
                                // New update available
                                console.log("New update available!");
                                // Optionally, show an update notification to the user
                            } else {
                                // No update available
                                console.log("Service worker installed for the first time!");
                            }
                            break;
                    }
                });
            });
        })
        .catch(err => {
            console.error('Service worker registration failed:', err);
        });

        let refreshing;
        // Detect controller change and refresh the page
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            if (refreshing) return;
            window.location.reload();
            refreshing = true;
        });
    });
}


let deferredPrompt;

window.addEventListener('beforeinstallprompt', (event) => {
    console.log('beforeinstallprompt fired');

    // Prevent Chrome 67 and earlier from automatically showing the prompt
    event.preventDefault();
    // Stash the event so it can be triggered later.
    deferredPrompt = event;
    // Update UI to notify the user they can add to home screen
    showInstallButton();

});


let isInstallButtonListenerAdded = false;

function showInstallButton() {
    const installButton = document.getElementById('pwa_install_button');
    installButton.hidden = false;

    if (!isInstallButtonListenerAdded) {
        installButton.addEventListener('click', installPWA);
        isInstallButtonListenerAdded = true;
    }
}

function installPWA() {
    // Show the prompt
    if (deferredPrompt) {
        deferredPrompt.prompt();
        // Wait for the user to respond to the prompt
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted the A2HS prompt');
            } else {
                console.log('User dismissed the A2HS prompt');
            }
            deferredPrompt = null;
        });
    }
}
