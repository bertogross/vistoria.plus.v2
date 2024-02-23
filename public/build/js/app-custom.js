import {
    toastAlert,
    lightbox,
    showPreloader,
    showButtonWhenInputChange
} from './helpers.js';

document.addEventListener('DOMContentLoaded', function() {
    //Overwrite the native app initModeSetting function
    const lightDarkModeBtn = document.getElementById("btn-light-dark-mode");
    if (lightDarkModeBtn) {
        lightDarkModeBtn.addEventListener('click', async function(event) {
            event.preventDefault();

            showPreloader();

            try {
                // Sleep for X miliseconds
                let ms = 10;
                await new Promise(resolve => setTimeout(resolve, ms));

                const html = document.getElementsByTagName("HTML")[0];

                if(html.hasAttribute("data-bs-theme") && html.getAttribute("data-bs-theme") == "dark"){
                    html.setAttribute("data-bs-theme", "light", "layout-mode-light", html);

                    var dataTheme = 'light';
                }else{
                    html.setAttribute("data-bs-theme", "dark", "layout-mode-dark", html);

                    var dataTheme = 'dark';
                }

                const url = profileChangeLayoutModeURL;
                const response = await fetch(url, {
                    method: 'POST',
                    body: JSON.stringify({ theme: dataTheme }),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (!data.success) {
                    toastAlert(data.message, 'danger');
                }

                showPreloader(false);

            } catch (error) {
                toastAlert('Error: ' + error, 'danger');

                showPreloader(false);

                console.error('Error:', error);
            }
        });
    }

    // Prevent data-choices sort companies by name
    const isChoiceCompanies = document.querySelectorAll(".filter-companies");
    if (isChoiceCompanies.length > 0) {
        Array.from(isChoiceCompanies).forEach(function (isChoice) {
            new Choices(isChoice, {
                shouldSort: false,
                removeItems: true,
                removeItemButton: true
            });
        });
    }


    const toggleConnections = document.querySelectorAll('.toggle-connection');
    toggleConnections.forEach(function(toggle) {
        toggle.addEventListener('change', async function() {
            if (this.checked) {

                showPreloader();

                //console.log('Selected Connection ID:', this.value);
                const connectionId = this.value;

                try {
                    // Sleep for X miliseconds
                    let ms = 10;
                    await new Promise(resolve => setTimeout(resolve, ms));

                    const url = profileChangeConnectionURL;
                    const response = await fetch(url, {
                        method: 'POST',
                        body: JSON.stringify({ id: connectionId }),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) {
                        showPreloader(false);

                        throw new Error('Network response was not ok: ' + response.statusText);
                    }

                    const data = await response.json();

                    if(data.success){
                        toastAlert(data.message, 'info');

                        location.reload(true);
                    }else{
                        toastAlert(data.message, 'danger');

                        showPreloader(false);
                    }
                } catch (error) {
                    toastAlert('Error: ' + error, 'danger');

                    showPreloader(false);

                    console.error('Error:', error);
                }
            }
        });
    });


});

// Check the internet connection status and display a toast notification if offline
function checkInternetConnection() {
    function updateConnectionStatus() {
        if (navigator.onLine) {
            //console.log('Online');
        } else {
            //console.log('Offline');
            toastAlert('A conexão foi perdida. Por favor, verifique sua rede de internet.', 'error', 100000, true);
        }
    }

    // Initial check
    updateConnectionStatus();

    // Set up event listeners for online and offline events
    window.addEventListener('online', function () {
        //console.log('Back online');
        toastAlert('A conexão foi reestabelecida', 'success', 5000);
        updateConnectionStatus();
    });

    window.addEventListener('offline', function () {
        //console.log('Lost connection');
        toastAlert('A conexão foi perdida. Por favor, verifique sua rede de internet.', 'error', 100000, true);
        updateConnectionStatus();
    });

    // Set up an interval to check the connection status periodically
    setInterval(updateConnectionStatus, 10000); // Check every 10 seconds
}

// To prevent users from entering HTML tags in various types of input fields
function sanitizeInputValue(inputElement) {
    let sanitizedValue = inputElement.value;

    // Define a regular expression pattern to match unwanted characters
    const unwantedCharsPattern = /[<>'"\\`]/g;

    // Store the original value before sanitization
    const originalValue = sanitizedValue;

    // Replace unwanted characters with an empty string
    sanitizedValue = sanitizedValue.replace(unwantedCharsPattern, '');

    // Update the input field's value with the sanitized text
    inputElement.value = sanitizedValue;

    // Check if any invalid characters were removed
    if (originalValue !== sanitizedValue) {
        setTimeout(() => {

            // Get the characters that were removed
            const removedChars = originalValue.match(unwantedCharsPattern).join('');

            // Invalid characters were removed, show an alert
            toastAlert(`O caractere <span class="text-danger fw-bold fs-14">${removedChars}</span> é inaceitável e foi removido`, 'info', 5000);
        }, 500);
    }
}
function sanitizeInputOnInput(event) {
    const target = event.target;

    // Check if the event target is a textarea or an input of type text
    if ((target.tagName === 'TEXTAREA') || (target.tagName === 'INPUT' && target.type === 'text')) {
        setTimeout(() => {
            sanitizeInputValue(target);
        }, 100);
    }
}
document.addEventListener('input', sanitizeInputOnInput);
document.addEventListener('blur', sanitizeInputOnInput);
document.addEventListener('change', sanitizeInputOnInput);
document.addEventListener('keyup', sanitizeInputOnInput);


// Prevent right-click context menu.
function preventRightClick(event) {
    event.preventDefault();
}
document.addEventListener("DOMContentLoaded", function () {
    if (document.body.classList.contains("production")) {
        document.addEventListener('contextmenu', preventRightClick);
    }
});




// Alert users when they try to type in an input or textarea element that is marked as readonly
/*
function handleReadonlyInputs() {
    const readonlyInputs = document.querySelectorAll("input[readonly], textarea[readonly]");

    readonlyInputs.forEach(function (input) {
        input.addEventListener("input", function () {
            toastAlert(`O campo <span class="text-danger fw-bold">${input.value}</span> é somente leitura e não pode ser editado`, 'info', 5000);
        });

        input.addEventListener("click", function () {
            toastAlert(`O campo <span class="text-danger fw-bold">${input.value}</span> é somente leitura e não pode ser editado`, 'info', 5000);
        });
    });
}
document.addEventListener('DOMContentLoaded', handleReadonlyInputs);
*/


// Call the functions when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', lightbox);
document.addEventListener('DOMContentLoaded', checkInternetConnection);
document.addEventListener('DOMContentLoaded', showButtonWhenInputChange);

document.addEventListener('load', function() {

    self.addEventListener('install', event => {
        self.skipWaiting(); // Force the waiting service worker to become the active service worker.
    });

    self.addEventListener('activate', event => {
        clients.claim(); // Take control of all clients as soon as the service worker is activated.
        // Consider clearing old caches here
    });

    // In your app's JavaScript
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register(assetURL + 'build/js/service-worker.js?v='+appVersion).then(reg => {
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
                            // Show an update notification to the user
                        }
                        break;
                    }
                });
            });
        });

        let refreshing;
        // Detect controller change and refresh the page
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            if (refreshing) return;
            window.location.reload();
            refreshing = true;
        });
    }



});



// Installing the PWA on the device
//https://pwa-workshop.js.org/5-pwa-install/#add-an-installation-button
let deferredPrompt; // Allows to show the install prompt
const installButton = document.getElementById("pwa_install_button");

if(installButton){
    function installPWA() {
        // Show the prompt
        deferredPrompt.prompt();
        installButton.disabled = true;

        // Wait for the user to respond to the prompt
        deferredPrompt.userChoice.then(choiceResult => {
            if (choiceResult.outcome === "accepted") {
                console.log("PWA setup accepted");
                installButton.hidden = true;
            } else {
                console.log("PWA setup rejected");
            }
            installButton.disabled = false;
            deferredPrompt = null;
        });
    }

    window.addEventListener("beforeinstallprompt", e => {
        e.preventDefault(); // Prevent Chrome 76 and earlier from automatically showing a prompt

        console.log("beforeinstallprompt fired");

        // Stash the event so it can be triggered later.
        deferredPrompt = e;

        // Show the install button
        installButton.hidden = false;
        installButton.addEventListener("click", installPWA);
    });

    window.addEventListener("appinstalled", evt => {
        console.log("appinstalled fired", evt);

    });
}
