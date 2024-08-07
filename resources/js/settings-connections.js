import {
    toastAlert,
    showPreloader,
    swalWithBootstrapButtons
} from './helpers.js';

document.addEventListener('DOMContentLoaded', function() {

    // Switch status account connection by quest action
    const toggleStatusConnection = document.querySelectorAll('.toggle-status-connection');
    if(toggleStatusConnection){
        toggleStatusConnection.forEach(function(toggle) {
            ['click'].forEach(eventType => {
                toggle.addEventListener(eventType, async function(event) {
                    event.preventDefault();

                    const thisRadio = this;

                    const hostId = thisRadio.value;
                    const questStatus = thisRadio.getAttribute('data-quest-status');

                    if(!hostId || !questStatus){
                        toastAlert('Não foi possível executar esta solicitação', 'danger');

                        return;
                    }

                    if(questStatus == 'active'){
                        var titleText = 'Revogar Conexão?';
                        var htmlText = 'Seu acesso será imediatamente desativado.';
                        var buttonText = 'Sim, revogar';
                        var buttonClass = 'btn-outline-danger';
                    } else if(questStatus == 'revoked'){
                        var titleText = 'Reativar Conexão?';
                        var buttonText = 'Sim, reativar';
                        var htmlText = 'Seu acesso será imediatamente restabelecido.';
                        var buttonClass = 'btn-outline-success';
                    }else{
                        toastAlert('Não foi possível executar esta solicitação', 'danger');

                        return;
                    }

                    var swalWithBootstrap = swalWithBootstrapButtons();
                    swalWithBootstrap.fire({
                        title: titleText,
                        html: htmlText,
                        icon: 'question',
                        buttonsStyling: false,
                        confirmButtonText: buttonText,
                            confirmButtonClass: 'btn ' + buttonClass + ' w-xs me-2',
                        cancelButtonText: 'Deixar como está',
                                showCancelButton: true,
                        denyButtonText: 'Não',
                                showDenyButton: false,
                        showCloseButton: false,
                        allowOutsideClick: false
                    }).then(async function (result) {
                        if (result.isConfirmed) {

                            showPreloader();

                            try {
                                // Sleep for X miliseconds
                                let ms = 10;
                                await new Promise(resolve => setTimeout(resolve, ms));

                                const url = revokeConnectionURL;
                                const response = await fetch(url, {
                                    method: 'POST',
                                    body: JSON.stringify({ id: hostId }),
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
                                    if(data.status == 'active'){
                                        thisRadio.checked = true;
                                        thisRadio.setAttribute("data-quest-status", data.status);

                                        toastAlert(data.message, 'success');
                                    }else{
                                        thisRadio.checked = false;
                                        thisRadio.setAttribute("data-quest-status", data.status);

                                        toastAlert(data.message, 'warning');
                                    }

                                    showPreloader();
                                    setTimeout(() => {
                                        location.reload();
                                    }, 2000);
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
        });
    }




});


