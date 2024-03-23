
// Installing the PWA
// https://developer.mozilla.org/en-US/docs/Web/API/Window/beforeinstallprompt_event
let deferredPrompt; // Allows to show the install prompt
let installPrompt = null;

const installButton = document.getElementById("pwa_install_button");
if(installButton){
    window.addEventListener("beforeinstallprompt", event => {
        event.preventDefault(); // Prevent Chrome 76 and earlier from automatically showing a prompt

        //console.log("beforeinstallprompt fired");

        // Stash the event so it can be triggered later.
        deferredPrompt = event;

        installPrompt = event;

        // Show the install button
        //installButton.hidden = false;
        installButton.removeAttribute("hidden");
    });

    installButton.addEventListener("click", async () => {
        if (!installPrompt) {
          return;
        }
        const result = await installPrompt.prompt();
        //console.log(`Install prompt was: ${result.outcome}`);

        deferredPrompt = null;

        installPrompt = null;

        installButton.setAttribute("hidden", "");
      });

    window.addEventListener("appinstalled", event => {
        //console.log("appinstalled fired", event);

        // The app was installed successfully, hide the install button
        installButton.setAttribute("hidden", true);
    });
}

// Additional heuristic to hide the install button for returning users or if the app is in standalone mode.
if (window.matchMedia('(display-mode: standalone)').matches) {
    //console.log("This is running as a PWA.");
    if (installButton) {
        installButton.setAttribute("hidden", true);
    }
}

/*
// DEPRECATED
// "Refresh on swipe down" feature
function refreshOnSwipeDown(){
    let touchStartY = 0;
    let touchEndY = 0;

    // Threshold for swipe action
    const threshold = 150;

    function handleTouchStart(event) {
        touchStartY = event.touches[0].clientY;
    }

    function handleTouchMove(event) {
        touchEndY = event.touches[0].clientY;
    }

    function handleTouchEnd() {
        if (touchStartY < touchEndY && (touchEndY - touchStartY) > threshold) {
            // Swipe down action detected
            //console.log('Swipe down detected. Refreshing page...');
            showPreloader();
            window.location.reload();
        }
    }

    // Add event listeners
    document.addEventListener('touchstart', handleTouchStart, false);
    document.addEventListener('touchmove', handleTouchMove, false);
    document.addEventListener('touchend', handleTouchEnd, false);
}
document.addEventListener('DOMContentLoaded', refreshOnSwipeDown);
*/
