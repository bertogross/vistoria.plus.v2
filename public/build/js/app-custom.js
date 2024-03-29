import {
    toastAlert,
    lightbox,
    showPreloader,
    bsPopoverTooltip,
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

                const url = changeLayoutModeURL;
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

    // Toogle account connections
    const toggleConnections = document.querySelectorAll('.toggle-connection');
    if(toggleConnections){
        toggleConnections.forEach(function(toggle) {
            ['click'].forEach(eventType => { // ['click', 'change']
                toggle.addEventListener(eventType, async function(event) {
                    event.preventDefault();

                    if (this.checked) {
                    }
                    const connectionId = this.value;
                    if(!connectionId){
                        toastAlert('Não foi possível executar esta solicitação', 'danger');
                        return;
                    }

                    showPreloader();

                    try {
                        // Sleep for X miliseconds
                        let ms = 10;
                        await new Promise(resolve => setTimeout(resolve, ms));

                        const url = changeConnectionURL;
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

                            setTimeout(() => {
                                //window.location.reload();
                                //location.reload(true);
                                window.location.href = '/';
                            }, 500);
                        }else{
                            toastAlert(data.message, 'danger');

                            showPreloader(false);
                        }

                        return;
                    } catch (error) {
                        toastAlert('Error: ' + error, 'danger');

                        showPreloader(false);

                        console.error('Error:', error);
                    }
                });
            });
        });
    }

    const btnInvitationConnectionDecision = document.querySelectorAll('.btn-accept-invitation');
    if(btnInvitationConnectionDecision.length && acceptOrDeclineConnectionURL){
        btnInvitationConnectionDecision.forEach(function(button) {
            button.addEventListener('click', async function(event) {
                event.preventDefault();

                const hostId = this.getAttribute('data-host-id');
                const hostName = this.getAttribute('data-host-name');

                var profileLink = "<a href='"+profileShowURL+"/"+hostId+"'><u>"+hostName+"</u></a>";
                Swal.fire({
                    title: 'Conexão',
                    html: 'Você recebeu um convite para colaborar com '+profileLink+'.<br>Você aceita a conexão?',
                    icon: 'question',
                    buttonsStyling: false,
                    confirmButtonText: 'Sim, conectar',
                        confirmButtonClass: 'btn btn-success w-xs me-2',
                    cancelButtonText: 'Aguardar',
                        cancelButtonClass: 'btn btn-sm btn-outline-warning w-xs',
                            showCancelButton: true,
                    denyButtonText: 'Recusar',
                        denyButtonClass: 'btn btn-sm btn-danger w-xs me-2',
                            showDenyButton: true,
                    showCloseButton: false,
                    allowOutsideClick: false
                }).then(async function (result) {
                    let acceptOrDecline;

                    if (result.isConfirmed) {
                        acceptOrDecline = 'accept';
                    } else if (result.isDenied) {
                        acceptOrDecline = 'decline';
                    } else {
                        event.stopPropagation();

                        toastAlert('Conexão não estabelecida', 'warning');

                        return;
                    }

                    showPreloader();

                    try {
                        const url = acceptOrDeclineConnectionURL;
                        const response = await fetch(url, {
                            method: 'POST',
                            body: JSON.stringify({ hostId: parseInt(hostId), decision: acceptOrDecline }),
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

                        Swal.close();

                        if (data.success) {
                            toastAlert(data.message, 'success');

                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        }else{
                            toastAlert(data.message, 'danger');

                            if(acceptOrDecline == 'decline'){
                                showPreloader();
                                setTimeout(() => {
                                    location.reload();
                                }, 2000);
                            }else{
                                showPreloader(false);
                            }
                        }
                    } catch (error) {
                        Swal.close();

                        toastAlert('Error: ' + error, 'danger');

                        showPreloader(false);

                        console.error('Error:', error);
                    }
                })
            });
        });
    }

    // Prevent users from submitting a form by hitting Enter
    const noEnterSubmit = document.querySelectorAll('.no-enter-submit');
    if(noEnterSubmit){
        noEnterSubmit.forEach(function(form) {
            form.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                }
            });
        });
    }


    //Check every X seconds if user is authorized to access account
    setInterval(function() {
        fetch('/check-authorization')
            .then(response => response.json()) // Always parse JSON to get the error message
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                //console.log('Authorization check successful', data);
            })
            .catch(error => {
                console.error('Authorization check failed', error.message);
                // Assuming toastAlert is a function you've defined to show error messages
                toastAlert(error.message, 'danger');
                // Consider the implications of reloading the page automatically
                setTimeout(() => {
                    window.location.reload();
                }, 4000);
            });
    }, 60000 * 5); // 60000 = 60 seconds


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

document.querySelectorAll('.init-loader').forEach(function(link) {
    link.addEventListener("click", function () {
        showPreloader();
    });
});

window.addEventListener("load", function () {
    showPreloader(false);
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
document.addEventListener('DOMContentLoaded', bsPopoverTooltip);

