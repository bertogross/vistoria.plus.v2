import {
    toastAlert,
    bsPopoverTooltip,
    showPreloader
} from './helpers.js';

import {
    attachImage
} from './settings-attachments.js';

document.addEventListener('DOMContentLoaded', function() {

    // Load the content for the user modal
    function loadUserSettingsModal(userId = null, userTitle = '') {
        var xhr = new XMLHttpRequest();

        var url = getUserFormContentURL;
        if (userId) {
            url += '/' + userId;
        }
        xhr.open('GET', url, true);
        xhr.setRequestHeader('Cache-Control', 'no-cache'); // Set the Cache-Control header to no-cache
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                if(xhr.responseText){
                    document.getElementById('modalContainer').innerHTML = xhr.responseText;

                    var modalElement = document.getElementById('userModal');
                    var modal = new bootstrap.Modal(modalElement, {
                        backdrop: 'static',
                        keyboard: false
                    });
                    modal.show();

                    if (userId) {
                        document.getElementById("modalUserTitle").innerHTML = userTitle ? 'Editar <span class="text-theme">'+ userTitle + '</span>' : 'Editar Usuário';
                        document.getElementById("btn-save-user").innerHTML = 'Atualizar Usuário';

                        injectScript("/build/js/pages/password-addon.init.js");
                    } else {
                        document.getElementById("modalUserTitle").innerHTML = 'Novo Usuário';
                        document.getElementById("btn-save-user").innerHTML = 'Adicionar Usuário';
                    }

                    attachModalEventListeners();

                    attachImage("#member-image-input", "#avatar-img", uploadAvatarURL);
                    attachImage("#cover-image-input", "#cover-img", uploadCoverURL);

                    bsPopoverTooltip();

                }else{
                    toastAlert('Não foi possível carregar o conteúdo', 'danger', 10000);
                }

            } else {
                console.log("Fetching modal content:", xhr.statusText);
            }
        };
        xhr.send();
    }

    // Event listener for the 'Add User' button
    if(document.getElementById('btn-add-user')){
        document.getElementById('btn-add-user').addEventListener('click', function() {
            loadUserSettingsModal();
        });
    }

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

    // Function to inject a script into the page
    function injectScript(src) {
        var script = document.createElement('script');
        script.src = src;
        document.body.appendChild(script);
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
        const btn = document.getElementById('btn-save-user');

        if (btn) {
            btn.addEventListener('click', function(event) {
                event.preventDefault();

                if (!form.checkValidity()) {
                    event.stopPropagation();
                    form.classList.add('was-validated');

                    toastAlert('Preencha os campos obrigatórios', 'danger', 5000);

                    return;
                }

                let formData = new FormData(form);

                let url = form.dataset.id ? settingsUsersUpdateURL + `/${form.dataset.id}` : settingsUsersStoreURL;

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
                        toastAlert(data.message, 'success', 20000);

                        if(form.dataset.id){
                            showPreloader();
                        }

                        setTimeout(() => {
                            location.reload();
                        }, 5000);

                        document.getElementById('btn-save-user').remove();
                    } else {
                        toastAlert(data.message, 'danger', 60000);
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

