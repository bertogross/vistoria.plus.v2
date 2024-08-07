// Installing the PWA
// https://developer.mozilla.org/en-US/docs/Web/API/Window/beforeinstallprompt_event
document.addEventListener('DOMContentLoaded', () => {
    let deferredPrompt; // Allows to show the install prompt
    let installPrompt = null;

    const installButton = document.getElementById("pwa_install_button");

    if (installButton) {
        // Hide the button initially
        installButton.setAttribute("hidden", "");

        // Check if the browser supports the beforeinstallprompt event
        if ('onbeforeinstallprompt' in window) {
            window.addEventListener("beforeinstallprompt", event => {
                event.preventDefault(); // Prevent Chrome 76 and earlier from automatically showing a prompt

                // Stash the event so it can be triggered later.
                deferredPrompt = event;
                installPrompt = event;

                // Show the install button
                installButton.removeAttribute("hidden");
            });

            installButton.addEventListener("click", async () => {
                if (!installPrompt) {
                    return;
                }
                const result = await installPrompt.prompt();
                // console.log(`Install prompt was: ${result.outcome}`);

                deferredPrompt = null;
                installPrompt = null;

                installButton.setAttribute("hidden", "");
            });

            window.addEventListener("appinstalled", event => {
                // console.log("appinstalled fired", event);

                // The app was installed successfully, hide the install button
                installButton.setAttribute("hidden", true);
            });

            // Additional heuristic to hide the install button for returning users or if the app is in standalone mode.
            if (window.matchMedia('(display-mode: standalone)').matches) {
                // console.log("This is running as a PWA.");
                installButton.setAttribute("hidden", true);
            }
        }
    }
});
