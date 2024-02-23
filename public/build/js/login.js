import {
    toastAlert,
    showPreloader
} from './helpers.js';

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btn-login').addEventListener('click', function(event) {
        event.preventDefault();

        let email = document.getElementById('username').value;
        let password = document.getElementById('password-input').value;

        if(!email){
            toastAlert('Informe o e-mail', 'danger', 5000);
            return;
        }

        if(!password){
            toastAlert('Informe a senha', 'danger', 5000);
            return;
        }

        showPreloader();
        document.getElementById('loginForm').submit();
    });

});
