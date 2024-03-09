import {
    toastAlert,
    showPreloader
} from './helpers.js';

document.addEventListener('DOMContentLoaded', function() {

    document.getElementById('btn-register').addEventListener('click', function(event) {
        event.preventDefault();

        let email = document.getElementById('useremail').value;
        let name = document.getElementById('username').value;

        if(!email && !name){
            toastAlert('Preencha o formulário', 'danger', 5000);
            return;
        }

        if(!email){
            toastAlert('Informe o e-mail', 'danger', 5000);
            return;
        }

        if(!name){
            toastAlert('Informe seu nome', 'danger', 5000);
            return;
        }

        showPreloader();
        document.getElementById('registerForm').submit();
    });

});