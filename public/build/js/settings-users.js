import {
    toastAlert,
    sweetAlert,
    sweetWizardAlert,
    bsPopoverTooltip,
    showPreloader,
    injectScript,
    multipleModal
} from './helpers.js';

import {
    attachImage
} from './settings-attachments.js';

document.addEventListener('DOMContentLoaded', function() {

    // Load the content for the user modal
    function loadUserSettingsModal(userId = null, userTitle = '', origin = null) {
        var xhr = new XMLHttpRequest();

        showPreloader();

        var url = getUserFormContentURL;

        // Create FormData and append parameters
        var formData = new FormData();
        if (userId !== null) formData.append('userId', userId);
        if (origin !== null) formData.append('origin', origin);

        // Retrieve CSRF token from meta tag
        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        formData.append('_token', token);

        xhr.open('POST', url, true);
        xhr.setRequestHeader('Cache-Control', 'no-cache'); // Set the Cache-Control header to no-cache
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                if(xhr.responseText){

                    document.getElementById('modalContainer2').innerHTML = xhr.responseText;

                    var modalElement = document.getElementById('userModal');
                    var modal = new bootstrap.Modal(modalElement, {
                        backdrop: origin ? false : 'static',
                        keyboard: false
                    });
                    modal.show();

                    multipleModal();

                    var modalUserTitle = document.getElementById("modalUserTitle");
                    var btnSaveUser = document.getElementById("btn-save-user");

                    if (userId) {
                        modalUserTitle.innerHTML = userTitle ? 'Editar <span class="text-theme">'+ userTitle + '</span>' : 'Editar Usuário';
                        btnSaveUser.innerHTML = 'Atualizar';

                        injectScript("/build/js/pages/password-addon.init.js");
                    } else if (origin == 'survey') {
                        modalUserTitle.innerHTML = 'Convidar Usuário';
                        btnSaveUser.innerHTML = 'Enviar Convite';
                    } else {
                        modalUserTitle.innerHTML = 'Novo Usuário';
                        btnSaveUser.innerHTML = 'Adicionar';
                    }

                    bsPopoverTooltip();

                    attachModalEventListeners();

                    attachImage("#member-image-input", "#avatar-img", uploadAvatarURL);
                    attachImage("#cover-image-input", "#cover-img", uploadCoverURL);
                }else{
                    toastAlert('Não foi possível carregar o conteúdo', 'danger', 10000);
                }

            } else {
                console.log("Fetching modal content:", xhr.statusText);
            }

            showPreloader(false);
        };
        xhr.send(formData);
    }

    // Event listener for the 'Add User' button
    /*var addButton = document.getElementById('btn-add-user');
    if(addButton){
        addButton.addEventListener('click', function() {
            loadUserSettingsModal();
        });
    }*/
    document.body.addEventListener('click', function(event) {
        // Check if the clicked element has the ID 'btn-add-user'
        if (event.target && event.target.id === 'btn-add-user') {
            var origin = event.target.getAttribute('data-origin');

            loadUserSettingsModal('', '', origin);
        }
    });

    // Event listeners for each 'Edit User' button
    var editButtons = document.querySelectorAll('.btn-edit-user');
    if(editButtons){
        editButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();

                var userId = this.getAttribute('data-user-id');
                var userTitle = this.getAttribute('data-user-title');

                loadUserSettingsModal(userId, userTitle);
            });
        });
    }


    // Search functionality for the user list
    const searchInput = document.getElementById('searchMemberList');
    if(searchInput){
        searchInput.addEventListener('keyup', function() {
            var searchTerm = this.value.toLowerCase();
            var users = document.querySelectorAll('[data-search-user-id]');

            if(users){
                users.forEach(function(user) {
                    var userName = user.getAttribute('data-search-user-name').toLowerCase();
                    // var userRole = user.getAttribute('data-search-user-role').toLowerCase();

                    //if (userName.includes(searchTerm) || userRole.includes(searchTerm)) {
                    if (userName.includes(searchTerm)) {
                        user.style.display = ''; // Show the user
                    } else {
                        user.style.display = 'none'; // Hide the user
                    }
                });
            }
        });
    }

    // Attach event listeners for the modal form
    function attachModalEventListeners() {

        // Update/Save user from modal form
        const form = document.getElementById('userForm');
        const btnSaveUser = document.getElementById('btn-save-user');

        if (btnSaveUser && form) {

            btnSaveUser.addEventListener('click', function(event) {
                event.preventDefault();

                const origin = btnSaveUser.getAttribute('data-origin');

                const userId = form.querySelector('input[name="user_id"]').value;


                if (!form.checkValidity()) {
                    event.stopPropagation();
                    form.classList.add('was-validated');

                    toastAlert('Preencha os campos obrigatórios', 'danger', 5000);

                    return;
                }

                let formData = new FormData(form);


                //let url = form.dataset.user_id ? settingsUsersUpdateURL + `/${form.dataset.user_id}` : settingsUsersStoreURL;
                let url = userId ? settingsUsersUpdateURL + '/' + userId : settingsUsersStoreURL;

                fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {

                        if(origin == 'survey'){
                            sweetAlert(data.message, 'Ok!', 'success');

                            // Access surveyReloadUsersTab directly from surveys.js
                            if (window.surveyReloadUsersTab) {
                                window.surveyReloadUsersTab(origin);
                            }

                        }else{
                            toastAlert(data.message, 'success', 20000);

                            // reload page
                            if(form.dataset.id){
                                showPreloader();
                            }

                            setTimeout(() => {
                                location.reload();
                            }, 5000);

                            btnSaveUser.remove();
                        }

                    } else if( data.action == 'subscriptionAlert'){
                        sweetWizardAlert(data.message, settingsIndexURL + '?tab=subscription', 'warning', 'Voltar', 'Ativar Assinatura')
                    } else {
                        sweetAlert(data.message);
                    }
                })
                .catch(error => {
                    toastAlert('Error: ' + error, 'danger', 60000);
                    console.error('Error:', error);
                });
            });
        }
    }


    // Filter functionality for switching between list and grid views
    var list = document.querySelectorAll(".team-list");
    if (list) {
        var buttonGroups = document.querySelectorAll('.filter-button');
        if (buttonGroups) {
            Array.from(buttonGroups).forEach(function (btnGroup) {
                btnGroup.addEventListener('click', onButtonGroupClick);
            });
        }
    }

    // This block handles the switch between list and grid views
    function onButtonGroupClick(event) {
        if (event.target.id === 'list-view-button' || event.target.parentElement.id === 'list-view-button') {
            document.getElementById("list-view-button").classList.add("active");
            document.getElementById("grid-view-button").classList.remove("active");
            Array.from(list).forEach(function (el) {
                el.classList.add("list-view-filter");
                el.classList.remove("grid-view-filter");
            });

        } else {
            document.getElementById("grid-view-button").classList.add("active");
            document.getElementById("list-view-button").classList.remove("active");
            Array.from(list).forEach(function (el) {
                el.classList.remove("list-view-filter");
                el.classList.add("grid-view-filter");
            });
        }
    }
    // End Filter functionality

});

