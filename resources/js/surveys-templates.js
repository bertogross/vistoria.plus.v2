import {
    toastAlert,
    sweetWizardAlert,
    maxLengthTextarea,
    ajaxContentFromURL,
    revalidationOnInput,
    allowUncheckRadioButtons,
    goTo,
    showPreloader,
    showButtonWhenInputChange
} from './helpers.js';

import {
    addTerms
} from './surveys-terms.js';

document.addEventListener('DOMContentLoaded', function() {

    // store/update surveyTemplateForm
    document.addEventListener('click', async function(event) {

        // The event.target contains the clicked element
        const clickedElement = event.target;
        //console.log('Clicked element:', clickedElement);

        if(clickedElement){

            const clickedElementId = clickedElement.id;
            //console.log(clickedElementId);

            // store/update surveyTemplateForm
            if ( clickedElementId === 'btn-survey-template-store-or-update' || clickedElementId === 'btn-survey-template-autosave' ) {
                event.preventDefault();

                const form = document.getElementById('surveyTemplateForm');
                if (!form) {
                    console.error('Form not found');
                    return;
                }


                var buttonSelectAnother = document.getElementById('btn-select-another-template');
                if(buttonSelectAnother){
                    buttonSelectAnother.remove();
                }

                // Check if form has topic fields
                const countTopics = form.querySelectorAll('.input-topic');
                if(!countTopics){
                    alert('Modelo deve conter Tópicos');
                    return;
                }

                const checkAutosave = document.getElementById(''+clickedElementId+'').getAttribute('data-autosave');

                //const choiceContainers = form.querySelectorAll('.choices__inner');

                if (!form.checkValidity()) {
                    if(checkAutosave === 'no'){
                        event.stopPropagation();

                        form.classList.add('was-validated');

                        /*if(choiceContainers){
                            choiceContainers.forEach(container => {
                                let select = container.parentElement.querySelector('select');
                                if (select && !select.checkValidity()) {
                                    container.classList.add('is-invalid');
                                }
                                if (select && select.checkValidity()) {
                                    container.classList.add('is-valid');
                                }
                            });
                        }*/

                        toastAlert('Preencha os campos obrigatórios', 'danger', 5000);

                        return;
                    }
                }else{
                    form.classList.remove('was-validated');

                    /*choiceContainers.forEach(container => {
                        container.classList.remove('is-invalid');
                        container.classList.remove('is-valid');
                    });*/
                }

                // Prevent to submit choices input
                /*var searchInput = document.querySelectorAll('.choices__input--cloned');
                if (searchInput) {
                    searchInput.forEach(function (choicesSearchTermsInput) {
                        choicesSearchTermsInput.disabled = true;
                    });
                }*/


                // Validate ID
                const surveyTemplateId = form.querySelector('input[name="id"]').value;

                const formData = new FormData(form);

                // Transform data
                var data = {};
                formData.forEach((value, key) => {
                    // Handle array formation for keys with multiple values
                    if (data.hasOwnProperty(key)) {
                        if (!Array.isArray(data[key])) {
                            data[key] = [data[key]];
                        }
                        data[key].push(value);
                    } else {
                        data[key] = value;
                    }
                });
                //console.log(data);
                //return;

                const transformedData = [];
                for (let i = 0; data.hasOwnProperty(`steps[${i}]['stepData']['term_name']`); i++) {
                    const stepData = {
                        term_name: data[`steps[${i}]['stepData']['term_name']`],
                        term_id: data[`steps[${i}]['stepData']['term_id']`],
                        type: data[`steps[${i}]['stepData']['type']`],
                        original_position: parseInt(data[`steps[${i}]['stepData']['original_position']`], 10),
                        new_position: parseInt(data[`steps[${i}]['stepData']['new_position']`], 10)
                    };

                    const topics = [];
                    const questions = data[`steps[${i}]['topics']['question']`];
                    const topicLength = Array.isArray(questions) ? questions.length : (questions ? 1 : 0);
                    if(topicLength){
                        for (let j = 0; j < topicLength; j++) {
                            //const question = data[`steps[${i}]['topics']['question']`][j];
                            const theQuestion = Array.isArray(questions) ? questions[j] : questions;
                            const originalPosition = data[`steps[${i}]['topics']['original_position']`][j];
                            const newPosition = data[`steps[${i}]['topics']['new_position']`][j];
                            if(theQuestion){
                                const topic = {
                                    question: theQuestion,
                                    new_position: parseInt(newPosition, 10),
                                    original_position: parseInt(originalPosition, 10)
                                };
                                topics.push(topic);
                            }
                        }
                    }

                    transformedData.push({ stepData, topics });
                }
                //console.log(transformedData);
                //console.log(JSON.stringify(transformedData, null, 2));
                //return;

                showPreloader();

                formData.append('template_data', JSON.stringify(transformedData, null, 2));

                try {
                    let url = surveyTemplateId ? surveysTemplateStoreOrUpdateURL + `/${surveyTemplateId}` : surveysTemplateStoreOrUpdateURL;

                    const response = await fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) {
                        showPreloader(false);

                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();

                    if (data.success) {
                        // Add value to input id
                        document.querySelector('input[name="id"]').value = data.id;

                        // Make the preview request
                        ajaxContentFromURL(data.id, surveysTemplatePreviewFromSurveyTemplatesURL);

                        if(checkAutosave === 'no'){
                            toastAlert(data.message, 'success');

                            sweetWizardAlert(data.message, surveysIndexURL, 'success');
                        }
                    } else {
                        toastAlert(data.message, 'danger', 10000);
                    }

                    showPreloader(false);
                } catch (error) {
                    toastAlert('Error: ' + error, 'danger', 60000);
                    console.error('Error:', error);

                    showPreloader(false);
                }
            }

            if ( clickedElementId === 'btn-select-another-template' ) {
                var titleText = 'Escolher outro Modelo?';
                var htmlText = 'Os dados não salvos neste formulário serão perdidos';

                Swal.fire({
                    title: titleText,
                    html: htmlText,
                    icon: 'question',
                    buttonsStyling: false,
                    confirmButtonText: 'Prosseguir',
                        confirmButtonClass: 'btn btn-warning w-xs me-2',
                    cancelButtonText: 'Ficar',
                        cancelButtonClass: 'btn btn-sm btn-outline-info w-xs',
                            showCancelButton: true,
                    denyButtonText: 'Não',
                        denyButtonClass: 'btn btn-sm btn-danger w-xs me-2',
                            showDenyButton: false,
                    showCloseButton: false,
                    allowOutsideClick: false
                }).then(async function (result) {
                    if (result.isConfirmed) {
                        toastAlert('Recarregando...', 'info', 2000, true);

                        document.getElementById('load-template-preview').innerHTML = '';

                        document.getElementById('accordion-templates-label').style.display = 'block';
                        document.getElementById('accordion-templates').style.display = 'block';
                        document.getElementById('nested-compose-area').style.display = 'none';

                        goTo('accordion-templates-label');
                    }
                });

             }

            if ( clickedElementId === 'btn-start-empty-template' ) {
                toastAlert('Carregando...', 'info', 2000, true);

                document.getElementById('load-selected-template-form').innerHTML = '';

                document.getElementById('accordion-templates-label').style.display = 'none';
                document.getElementById('accordion-templates').style.display = 'none';
                document.getElementById('nested-compose-area').style.display = 'block';

                goTo('accordion-templates-label');
            }
        }
    });


    const previewFromWarehouseTemplateButtons = document.querySelectorAll('.btn-warehouse-template-preview');
    if(previewFromWarehouseTemplateButtons){
        previewFromWarehouseTemplateButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();

                var id = this.getAttribute("data-id");

                var title = this.getAttribute("data-title");
                document.getElementById('modal-template-title').innerHTML = '<span class="text-theme">Modelo:</span> '+title;

                document.getElementById('load-template-preview').innerHTML = '<div class="text-center"><div class="spinner-border text-theme" role="status"><span class="sr-only">Loading...</span></div></div>';

                const modalElement = document.getElementById('previewTemplateModal');
                const modal = new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false,
                });
                modal.show();

                ajaxContentFromURL(id, surveysTemplatePreviewFromWarehouseURL, 'load-template-preview');
            });
        });
    }


    const previewUserTemplateButtons = document.querySelectorAll('.btn-user-template-preview');
    if(previewUserTemplateButtons){
        previewUserTemplateButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();

                var id = this.getAttribute("data-id");

                var title = this.getAttribute("data-title");
                document.getElementById('modal-template-title').innerHTML = '<span class="text-theme">Modelo:</span> '+title;

                document.getElementById('load-template-preview').innerHTML = '<div class="text-center"><div class="spinner-border text-theme" role="status"><span class="sr-only">Loading...</span></div></div>';

                const modalElement = document.getElementById('previewTemplateModal');
                const modal = new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false,
                });
                modal.show();

                ajaxContentFromURL(id, surveysTemplatePreviewFromSurveyTemplatesURL, 'load-template-preview');
            });
        });
    }

    // Change template status to publish/filed
    const statusTemplateButtons = document.querySelectorAll('.btn-change-template-status');
    if(statusTemplateButtons){
        statusTemplateButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();

                const isConfirmed = confirm('Certeza que deseja alterar o status?');
                if (!isConfirmed) {
                    event.stopPropagation();

                    return;
                }

                var templateId = this.getAttribute("data-id");

                // Use only to change status to pending
                fetch(surveysTemplateChangeStatusURL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Laravel CSRF token
                    },
                    body: JSON.stringify({ id: templateId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {

                        if(data.status == 'publish'){
                            this.classList.remove('btn-outline-warning');
                            this.classList.add('btn-outline-success');
                            this.innerHTML = 'Ativo';

                            toastAlert(data.message, 'success', 3000, true);
                        }else{
                            this.classList.remove('btn-outline-success');
                            this.classList.add('btn-outline-warning');
                            this.innerHTML = 'Arquivado';

                            toastAlert(data.message, 'warning', 3000, true);
                        }

                    } else {
                        // Handle error
                        console.error('Error:', data.message);

                        toastAlert(data.message, 'danger', 5000);
                    }
                })
                .catch(error => console.error('Error:', error));

            });
        });
    }

    // Make the preview request after page load
    var idInput = document.querySelector('input[name="id"]');
    var idValue = idInput ? idInput.value : null;
    ajaxContentFromURL(idValue, surveysTemplatePreviewFromSurveyTemplatesURL);


    // Call the function when the DOM is fully loaded
    revalidationOnInput();
    maxLengthTextarea();
    allowUncheckRadioButtons();
    showButtonWhenInputChange();
    addTerms();
   // choicesListeners(surveysTermsSearchURL, surveysTemplateStoreOrUpdateURL, choicesSelectorClass);

});
