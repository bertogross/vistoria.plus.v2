import {
    toastAlert,
    sweetAlert,
    sweetWizardAlert,
    lightbox,
    showPreloader,
    debounce,
    updateProgressBar,
    updateLabelClassesAuditor,
    uncheckRadiosAndUpdateLabels
} from './helpers.js';

document.addEventListener('DOMContentLoaded', function() {

    // Event listeners for each 'btn-assignment-auditor-action' to change task status from current to the next
    // This Button are in resources\views\surveys\layouts\profile-surveyors-box.blade.php
    const assignmentActionAuditorButtons = document.querySelectorAll('.btn-assignment-auditor-action');
    if(assignmentActionAuditorButtons){
        assignmentActionAuditorButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();

                var surveyId = this.getAttribute("data-survey-id");
                surveyId = parseInt(surveyId);

                var assignmentId = this.getAttribute("data-assignment-id");
                assignmentId = parseInt(assignmentId);

                var currentStatus = this.getAttribute("data-current-status"); // new  |  pending  |  in_progress  |  auditing

                if(currentStatus == 'new'){
                    // Use only to change status to pending
                    fetch(changeAssignmentAuditorStatusURL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Laravel CSRF token
                        },
                        body: JSON.stringify({ assignment_id: assignmentId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            /*
                            toastAlert(data.message, 'success');

                            setTimeout(function () {
                                location.reload(true);
                            }, 1000);
                            */
                            toastAlert('Redirecionando ao formulário...', 'success');

                            setTimeout(function () {
                                window.location.href = formAuditorAssignmentURL + '/' +assignmentId;
                            }, 1000);
                        } else {
                            // Handle error
                            console.error('Error:', data.message);

                            toastAlert(data.message, 'danger', 5000);
                        }
                    })
                    .catch(error => console.error('Error:', error));
                 }else{
                    toastAlert('Redirecionando ao formulário...', 'success');

                    setTimeout(function () {
                        window.location.href = formAuditorAssignmentURL + '/' +assignmentId;
                    }, 1000);
                 }
            });
        });
    }

    const assignmentAuditEnterButtons = document.querySelectorAll('.btn-assignment-audit-enter');
    if(assignmentAuditEnterButtons){
        assignmentAuditEnterButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();

                this.blur();

                var assignmentId = this.getAttribute("data-assignment-id");
                assignmentId = parseInt(assignmentId);

                Swal.fire({
                    title: 'Deseja realizar esta Auditoria?',
                    icon: 'question',
                    confirmButtonText: 'Sim',
                        confirmButtonClass: 'btn btn-outline-secondary w-xs me-2',
                    cancelButtonText: 'Não',
                        cancelButtonClass: 'btn btn-sm btn-outline-danger w-xs',
                            showCancelButton: true,
                    denyButtonText: 'Nunca',
                        denyButtonClass: 'btn btn-outline-danger w-xs me-2',
                            showDenyButton: false,
                    buttonsStyling: false,
                    showCloseButton: false,
                    allowOutsideClick: false
                }).then(function (result) {
                    if (result.isConfirmed) {
                        fetch(enterAssignmentAuditorURL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Laravel CSRF token
                            },
                            body: JSON.stringify({ assignment_id: assignmentId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            var sweetMessage = data.message;

                            if (data.success) {
                                //toastAlert(data.message, 'success', 10000);

                                if(data.current_surveyor_status != 'completed'){
                                    sweetMessage += 'Esta tarefa ainda não foi concluída. Assim que disponível ela será exibida na sessão de seu Perfil.';

                                    sweetWizardAlert(sweetMessage, profileShowURL, 'warning', 'Ficar por aqui', 'Acessar meu Perfil');
                                }else{
                                    sweetMessage += '<br><br>Deseja acessar seu formulário agora?';

                                    sweetWizardAlert(sweetMessage, formAuditorAssignmentURL + '/' + assignmentId, 'success', 'Não', 'Sim, acessar');
                                }
                            } else {
                                // Handle error
                                //console.error('Error:', data.message);

                                var currentSurveyorStatus = data.current_surveyor_status;

                                sweetMessage = data.message;

                                if(data.action == 'request'){
                                    toastAlert(data.message, 'danger', 60000);

                                    // TODO ?
                                    //sweetWizardAlert(sweetMessage, requestAssignmentAuditorURL + '/' + assignmentId, 'info', 'Deixar como está', 'Solicitar esta Tarefa');
                                }else if(data.action == 'choice'){
                                    Swal.fire({
                                        title: "Atenção",
                                        html: sweetMessage,
                                        icon: 'info',
                                        confirmButtonText: 'Abrir formulário e Auditar',
                                            confirmButtonClass: 'btn btn-outline-secondary w-xs me-2',
                                        cancelButtonText: 'Deixar como está',
                                            cancelButtonClass: 'btn btn-sm btn-outline-info w-xs',
                                                showCancelButton: true,
                                        denyButtonText: 'Revogar',
                                            denyButtonClass: 'btn btn-sm btn-outline-danger w-xs me-2',
                                                showDenyButton: true,
                                        buttonsStyling: false,
                                        showCloseButton: false,
                                        allowOutsideClick: false
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            if( currentSurveyorStatus == 'completed' ){
                                                // redirect to form
                                                var timerInterval;
                                                Swal.fire({
                                                    title: 'Redirecionando...',
                                                    html: '',
                                                    timer: 1000,
                                                    timerProgressBar: true,
                                                    showCloseButton: false,
                                                    didOpen: function () {
                                                        Swal.showLoading()
                                                        timerInterval = setInterval(function () {
                                                            var content = Swal.getHtmlContainer()
                                                            if (content) {
                                                                var b = content.querySelector('b')
                                                                if (b) {
                                                                    b.textContent = Swal.getTimerLeft()
                                                                }
                                                            }
                                                        }, 100)
                                                    },
                                                    onClose: function () {
                                                        clearInterval(timerInterval)
                                                    }
                                                }).then(function (result) {
                                                    /* Read more about handling dismissals below */
                                                    if (result.dismiss === Swal.DismissReason.timer) {
                                                        //console.log('I was closed by the timer')

                                                        setTimeout(() => {
                                                            window.location.href = formAuditorAssignmentURL + '/' + assignmentId;
                                                        }, 100);
                                                    }
                                                });
                                            }else{
                                                toastAlert('A Vistoria ainda não foi concluída e por isso não será possível acessar o formulário de Auditoria.<br>Tente novamente mais tarde!', 'danger', 60000);

                                                sweetAlert('A Vistoria ainda não foi concluída e por isso não será possível acessar o formulário de Auditoria.<br>Tente novamente mais tarde!')
                                            }
                                        } else if (result.isDenied) {
                                            // do action to revogue
                                            fetch(revokeAssignmentAuditorURL + '/' + assignmentId, {
                                                method: 'POST',
                                                headers: {
                                                    'X-Requested-With': 'XMLHttpRequest',
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                                }
                                            })
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.success) {
                                                    toastAlert(data.message, 'success',5000);

                                                    showPreloader();

                                                    setTimeout(() => {
                                                        location.reload();
                                                    }, 2000);

                                                } else {
                                                    toastAlert(data.message, 'danger', 60000);
                                                }
                                            })
                                            .catch(error => {
                                                toastAlert('Error: ' + error, 'danger', 60000);
                                                console.error('Error:', error);
                                            });
                                        }
                                    });
                                }else{
                                    //toastAlert(data.message, 'danger', 60000);
                                    sweetAlert(data.message);
                                }
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    }
                })
            });
        });
    }


    // Event listeners for each 'btn-response-update' to update/store form data to the 'survey_responses' table
    const responseAuditorUpdateButtons = document.querySelectorAll('.btn-response-update');
    if(responseAuditorUpdateButtons){
        responseAuditorUpdateButtons.forEach(button => {
            button.addEventListener('click', event => {
                event.preventDefault();

                button.blur();

                const container = document.getElementById('assignment-container');
                if (!container) {
                    console.error('Container not found');
                    return;
                }

                const responsesDataContainer = button.closest('.responses-data-container');
                if (!responsesDataContainer) {
                    console.error('Responses data container not found');
                    return;
                }

                const textArea = responsesDataContainer.querySelector('textarea');
                const btnPhoto = responsesDataContainer.querySelector('.btn-add-photo');
                const btnsCompliance = responsesDataContainer.querySelectorAll('.btn-compliance');

                //const countTopics = document.querySelectorAll('.btn-response-update').length;
                //console.log('countTopics', countTopics);

                const surveyId = parseInt(container.querySelector('input[name="survey_id"]')?.value || 0);
                const companyId = parseInt(container.querySelector('input[name="company_id"]')?.value || 0);
                const assignmentId = parseInt(button.getAttribute('data-assignment-id'));
                const stepId = parseInt(button.getAttribute('data-step-id'));
                const topicId = parseInt(button.getAttribute('data-topic-id'));

                var responseId = responsesDataContainer.querySelector('input[name="response_id"]').value;

                const compliance = responsesDataContainer.querySelector('input[type="radio"][name="compliance_audit"]:checked')?.value || '';
                const radios = responsesDataContainer.querySelectorAll('input[type="radio"][name="compliance_audit"]');

                const comment = responsesDataContainer.querySelector('textarea[name="comment_audit"]')?.value || '';
                const attachmentInputs = responsesDataContainer.querySelectorAll('input[name="attachment_id[]"]');
                const attachmentIds = Array.from(attachmentInputs).map(input => input.value);

                // Select the radio buttons
                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        // When a radio button changes, update the label classes
                        updateLabelClassesAuditor(radios);
                    });
                });

                var pendingIcon = responsesDataContainer.querySelector('.ri-time-line');
                var completedIcon = responsesDataContainer.querySelector('.ri-check-double-fill');

                /*
                if ( compliance == 'no' && attachmentIds.length === 0 ) {

                    btnPhoto.classList.add('blink', 'bg-warning');
                    setTimeout(() => {
                        btnPhoto.classList.remove('blink', 'bg-warning');
                    }, 5000);

                    uncheckRadiosAndUpdateLabels(radios);

                    // If responseId is not set, show the pending icon and hide the completed icon
                    if (pendingIcon) pendingIcon.classList.remove('d-none');
                    if (completedIcon) completedIcon.classList.add('d-none');

                    document.querySelector('#btn-response-finalize').classList.add('d-none');
                }
                */

                const formData = {
                    assignment_id: assignmentId,
                    id: companyId,
                    survey_id: surveyId,
                    step_id: stepId,
                    topic_id: topicId,
                    compliance_audit: compliance,
                    comment_audit: comment,
                    attachment_ids: attachmentIds
                };
                //console.log(JSON.stringify(formData, null, 2));
                //return;

                var url = responsesAuditorStoreOrUpdateURL + '/' + responseId

                // AJAX call to store or update the 'survey_responses' table where step_id, topic_id, survey_id
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Laravel CSRF token
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    const countResponses = parseInt(data.countResponses || 0);
                    const countTopics = parseInt(data.countTopics || 0);
                    updateProgressBar(countResponses, countTopics, 'survey-progress-bar');

                    if (data.success) {
                        //toastAlert(data.message, 'success', 5000);

                        const responseId = data.id;
                        //const countFinishedTopics = parseInt(data.count || 0);
                        //console.log('countFinishedTopics', countFinishedTopics);

                        if (responseId) {
                            // If responseId is set, show the completed icon and hide the pending icon
                            if (pendingIcon) pendingIcon.classList.add('d-none');
                            if (completedIcon) completedIcon.classList.remove('d-none');

                            button.querySelector('i').classList.add('ri-refresh-line');
                            button.querySelector('i').classList.remove('ri-save-3-line');
                            button.setAttribute('title', 'Atualizar');
                            button.setAttribute('data-bs-original-title', 'Atualizar');
                        } else {
                            // If responseId is not set, show the pending icon and hide the completed icon
                            if (pendingIcon) pendingIcon.classList.remove('d-none');
                            if (completedIcon) completedIcon.classList.add('d-none');
                        }

                        /*if( countFinishedTopics >= countTopics ){
                            // enable button to finish
                            document.querySelector('#btn-response-finalize').classList.remove('d-none');
                        }*/
                    } else {
                        //console.log('Erro:', data.message);

                        button.querySelector('i').classList.remove('ri-refresh-line');
                        button.querySelector('i').classList.add('ri-save-3-line');

                        if(data.action == 'changeToPending'){
                            if (pendingIcon) pendingIcon.classList.remove('d-none');
                            if (completedIcon) completedIcon.classList.add('d-none');

                            document.querySelector('#btn-response-finalize').classList.add('d-none');
                        }

                        if(data.action2 == 'showTextarea'){
                            uncheckRadiosAndUpdateLabels(radios);

                            textArea.style.display = "block";

                            textArea.focus();

                            textArea.classList.add('blink', 'bg-warning-subtle');

                            setTimeout(() => {
                                textArea.classList.remove('blink', 'bg-warning-subtle');
                            }, 3000);
                        }else if(data.action2 == 'blinkPhotoButton'){
                            btnPhoto.classList.add('blink', 'bg-warning');

                            setTimeout(() => {
                                btnPhoto.classList.remove('blink', 'bg-warning');
                            }, 3000);

                            uncheckRadiosAndUpdateLabels(radios);
                        }else if(data.action2 == 'blinkComplianceButtons'){
                            if(btnsCompliance){
                                Array.from(btnsCompliance).forEach(function (btn) {
                                    btn.classList.add('blink');

                                    setTimeout(() => {
                                        btn.classList.remove('blink');
                                    }, 5000);
                                });
                            }
                        }

                        toastAlert(data.message, 'danger', 7000);
                    }

                    if(data.showFinalizeButton){
                        //setTimeout(() => {
                            document.querySelector('#btn-response-finalize').classList.remove('d-none');

                            sweetWizardAlert('Tarefa Concluída', false, 'success', 'Continuar Editando', 'Finalizar', '#btn-response-finalize');
                        //}, 1000);
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    }

    // Attach event listeners to compliance survey radio buttons
    function attachComplianceAuditorRadios(){
        const complianceAuditorRadios = document.querySelectorAll('input[name="compliance_audit"]');
        if(complianceAuditorRadios){
            complianceAuditorRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    const container = this.closest('.responses-data-container');

                    const updateButton = container.querySelector('.btn-response-update');
                    if (updateButton) {
                        updateButton.click();
                    }
                }, 1);
            });
        }
    }
    attachComplianceAuditorRadios();

    // Attach event listener to the comment textarea
    const commentAuditTextareas = document.querySelectorAll('textarea[name="comment_audit"]');
    if(commentAuditTextareas){
        commentAuditTextareas.forEach(textarea => {
            textarea.addEventListener('input', debounce(function() {
                const container = this.closest('.responses-data-container');

                const updateButton = container.querySelector('.btn-response-update');
                if (updateButton) {
                    updateButton.click();
                }
            }, 3000)); // 3000 milliseconds = 3 second
        });
    }

    // When Surveyor finish your taks, transfer to Auditor make revision
    const responseAuditorAssignmentFinalizedButton = document.getElementById('btn-response-finalize');
    if(responseAuditorAssignmentFinalizedButton){
        responseAuditorAssignmentFinalizedButton.addEventListener('click', async function(event) {
            event.preventDefault();

            const assignmentId = parseInt(this.getAttribute('data-assignment-id'));

            const container = document.getElementById('assignment-container');
            if (!container || !assignmentId) {
                console.error('Container or assignmentId not found');

                toastAlert('Ocorreu um erro. Atualize a sessão ou retorne mais tarde.', 'danger', 20000);

                return;
            }

            //Ajax to change 'surveys' table column status to 'auditing' and if the response is success call Swal.fire to redirect
            fetch(changeAssignmentAuditorStatusURL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Laravel CSRF token
                },
                body: JSON.stringify({ assignment_id: assignmentId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastAlert(data.message, 'success');

                    window.location.href = assignmentShowURL + '/' + assignmentId;

                } else {
                    // Handle error
                    console.error('Survey status error:', data.message);

                    toastAlert(data.message, 'danger', 5000);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    lightbox();


});
