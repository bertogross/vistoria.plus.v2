import {
    toastAlert,
    sweetAlert,
    sweetWizardAlert,
    initFlatpickr,
    maxLengthTextarea,
    ajaxContentFromURL,
    revalidationOnInput,
    multipleModal,
    bsPopoverTooltip,
    layouRightSide,
    toggleTableRows,
    getCookie,
    setCookie,
    removeCookie,
    showPreloader
} from './helpers.js';


document.addEventListener('DOMContentLoaded', function() {

    const btnCreate = document.getElementById('btn-surveys-create');
    if(btnCreate){
        btnCreate.addEventListener('click', async function(event) {
            event.preventDefault;

            loadSurveyFormModal();
        });
    }

    // Event listeners for each 'Edit' buttonS
    const editButtons = document.querySelectorAll('.btn-surveys-edit');
    if(editButtons){
        editButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();

                var surveyId = this.getAttribute("data-survey-id");

                loadSurveyFormModal(surveyId);
            });
        });
    }


    // Event listeners for each 'View' buttons
    /*
    const viewAssignmentButtons = document.querySelectorAll('.btn-assignment-view-form');
    if(viewAssignmentButtons && assignmentShowURL){
        viewAssignmentButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();

                var assignmentId = this.getAttribute('data-assignment-id');
                var assignmentTitle = this.getAttribute('data-assignment-title');

                document.getElementById('offcanvasRightLabel').innerHTML = assignmentTitle;

                ajaxContentFromURL(assignmentId, assignmentShowURL, 'load-assignment-result', '', 'content');

                var offcanvasElement = document.getElementById('assignmentResultOffcanvas');
                var offcanvasInstance = new bootstrap.Offcanvas(offcanvasElement);
                offcanvasInstance.show();


            });
        });
    }
    {{--
    <div class="offcanvas offcanvas-end" id="assignmentResultOffcanvas" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="offcanvasRightLabel" tabindex="-1" data-bs-scroll="true">
        <div class="offcanvas-header">
            <h5 id="offcanvasRightLabel">Offcanvas Right</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body" id="load-assignment-result">
            ...
        </div>
    </div>
    --}}
    */


    const changeStatusButtons = document.querySelectorAll('.btn-surveys-change-status');
    if(changeStatusButtons && surveysChangeStatusURL){
        changeStatusButtons.forEach(function(button) {
            button.addEventListener('click', async function(event) {
                event.preventDefault;

                var currentStatus = this.getAttribute("data-current-status");

                var surveyId = this.getAttribute("data-survey-id");
                surveyId = parseInt(surveyId);

                function attachSurveysChangeStatus(surveyId){
                    showPreloader();

                    fetch(surveysChangeStatusURL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Laravel CSRF token
                        },
                        body: JSON.stringify({ id: surveyId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            toastAlert(data.message, 'success');

                            location.reload(true);
                        } else {
                            // Handle error
                            //console.error('Error start/stop survey:', data.message);

                            if(data.action = 'userStatusAlert'){
                                sweetAlert(data.message);

                                showPreloader(false);

                                return;
                            }

                            toastAlert(data.message, 'danger', 5000);
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }

                if( currentStatus && currentStatus == 'started' ){
                    Swal.fire({
                        icon: 'warning',
                        title: "Tem certeza que deseja Interromper esta Tarefa?",
                        html: 'Interromper Tarefas em andamento terão suas respectivas atividades não completadas <strong>removidas</strong>. <br><br><strong class="text-warning">Não será possível reverter remoções.</strong>',
                        confirmButtonText: "Sim, interromper",
                            confirmButtonClass: 'btn btn-outline-danger w-xs me-2',
                                showCloseButton: false,
                        denyButtonText: `Deixar como está`,
                            denyButtonClass: 'btn btn-sm btn-outline-info w-xs me-2',
                                showDenyButton: true,
                        cancelButtonClass: 'btn btn-sm btn-outline-primary w-xs',
                            showCancelButton: false,
                        buttonsStyling: false,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            attachSurveysChangeStatus(surveyId);
                        } else if (result.isDenied) {
                            return false;
                        }
                    });
                }else{
                    attachSurveysChangeStatus(surveyId);
                }
            });
        });
    }

    function recurringOnce(){
        const recurringSelect = document.getElementById('date-recurring-field');
        const startDateInput = document.getElementById('date-recurring-start');
        const endDateInput = document.getElementById('date-recurring-end');

        if(recurringSelect){
            // Event listener for the recurring select dropdown
            recurringSelect.addEventListener('change', function() {
                if (this.value === 'once') {
                    // If 'once' is selected, disable the end date and set its value to the start date
                    endDateInput.value = startDateInput.value;
                    endDateInput.disabled = true;
                } else {
                    // For other options, enable the end date input
                    endDateInput.disabled = false;
                }
            });

            // Optional: Event listener for the start date to update the end date if 'once' is selected
            startDateInput.addEventListener('input', function() {
                if (recurringSelect.value === 'once') {
                    endDateInput.value = startDateInput.value;
                }
            });
        }
    }

    function loadSurveyFormModal(Id = null) {
        showPreloader();

        var xhr = new XMLHttpRequest();

        var url = Id ? surveysEditURL + '/' + Id : surveysCreateURL;

        xhr.open('GET', url, true);
        xhr.setRequestHeader('Cache-Control', 'no-cache'); // Set the Cache-Control header to no-cache
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                if(xhr.responseText){
                    document.getElementById('modalContainer').innerHTML = xhr.responseText;

                    var modalElement = document.getElementById('surveysModal');
                    var modal = new bootstrap.Modal(modalElement, {
                        backdrop: 'static',// 'static'
                        keyboard: false
                    });
                    modal.show();

                    attachModalEventListeners();

                    revalidationOnInput();

                    recurringOnce();

                    wizardFormSteps();

                    initFlatpickr();

                    bsPopoverTooltip();

                    multipleModal();

                }else{
                    toastAlert('Não foi possível carregar o conteúdo', 'danger', 10000);
                }

                showPreloader(false);
            } else {
                console.log("Fetching modal content:", xhr.statusText);

                showPreloader(false);
            }
        };
        xhr.send();
    }

    function wizardFormSteps(){
        var formSteps = document.querySelectorAll(".form-steps");

        if (formSteps){
            Array.from(formSteps).forEach(function (form) {
                surveyCheckAllFormCheckInputs();

                /*function checkRequiredFields(inputControlRequired) {
                    let filledRequiredFields = Array.from(inputControlRequired).reduce((count, elem) => {
                        return count + (elem.value.trim() !== '' ? 1 : 0);
                    }, 0);

                    return filledRequiredFields === inputControlRequired.length;
                }*/

                function navigateToTab(nextTabId) {
                    form.classList.remove('was-validated');

                    const nextTab = document.getElementById(nextTabId);

                    nextTab.removeAttribute('disabled');
                    nextTab.click();
                    nextTab.setAttribute('disabled', 'disabled');

                    //form.classList.add('was-validated');
                }

                function checkRequiredFields(inputControls, switchControls) {
                    let emptyInputCount = Array.from(inputControls).filter(input => !input.value.trim()).length;
                    let isSwitchChecked = Array.from(switchControls).filter(input => input.checked).length;

                    let switchRequirementCount = 0;

                    // If no switch is checked, count it as one requirement not met
                    if(isSwitchChecked){
                        switchRequirementCount = isSwitchChecked > 0 ? 0 : 1;
                    }

                    return emptyInputCount + switchRequirementCount;
                }

                // next tab
                if (form.querySelector(".nexttab")) {
                    const tabButtons = form.querySelectorAll('button[data-bs-toggle="pill"]');
                    Array.from(tabButtons).forEach(item => {
                        item.addEventListener('show.bs.tab', event => event.target.classList.add('done'));
                    });

                    Array.from(form.querySelectorAll(".nexttab")).forEach(nextButton => {
                        nextButton.addEventListener("click", () => {
                            form.classList.add('was-validated');

                            const activeTab = form.querySelector(".tab-pane.show");

                            const nextTab = nextButton.getAttribute('data-nexttab');

                            const inputControlRequired = activeTab.querySelectorAll(".wizard-input-control[required]");
                            //console.log('inputControlRequired', inputControlRequired.length);

                            const switchControlRequired = activeTab.querySelectorAll(".wizard-switch-control[required]");
                            //console.log('switchControlRequired', switchControlRequired.length);

                            const totalUnfilledRequired = checkRequiredFields(inputControlRequired, switchControlRequired);
                            //console.log('totalUnfilledRequired', totalUnfilledRequired);

                            if (totalUnfilledRequired > 0) {
                                toastAlert('Necessário preencher os campos obrigatórios', 'danger', 10000);
                                return;
                            }

                            const companyCheckboxes = form.querySelectorAll(".tab-pane.show .form-check-input-companies");

                            companyCheckboxes.forEach(function(checkbox) {
                                ['click', 'change'].forEach(eventType => {
                                    // Listen for changes on each company checkbox
                                    checkbox.addEventListener(eventType, function() {
                                        // Find the parent .card element
                                        const card = this.closest('.card');
                                        if (!card) return;

                                        // Find all user inputs within the same card
                                        const userInputs = card.querySelectorAll('.form-check-input-users');

                                        // If the company checkbox is checked, add 'required' to user inputs
                                        // Otherwise, remove 'required'
                                        userInputs.forEach(function(input) {
                                            if (checkbox.checked) {
                                                input.setAttribute('required', '');
                                            } else {
                                                input.removeAttribute('required');
                                            }
                                        });
                                    });
                                });
                            });

                            // Additional logic for specific tabs
                            if (nextTab === 'steparrow-template-info-tab') {

                                if (companyCheckboxes.length) {
                                    // Initialize a flag to keep track of whether any checkbox is checked
                                    let isChecked = false;

                                    // Iterate over all checkboxes to check if at least one is checked
                                    companyCheckboxes.forEach(function(checkbox) {
                                        if (checkbox.checked) {
                                            isChecked = true;
                                        }
                                    });

                                    // If no checkboxes are checked, show an alert
                                    if (!isChecked) {
                                        toastAlert('Necessário selecionar ao menos uma Unidade', 'danger', 10000);
                                        return;
                                    }
                                }

                                const usersCheckboxes = form.querySelectorAll(".tab-pane.show .form-check-input-users:checked");

                                const selectedCompanies = form.querySelectorAll(".tab-pane .form-check-input-companies:checked");

                                if (usersCheckboxes.length < selectedCompanies.length) {
                                    toastAlert('Delegue para cada Unidade Ativa as respectivas Atribuições', 'danger', 10000);

                                    return;
                                }

                                navigateToTab(nextTab);

                            } else {
                                navigateToTab(nextTab);
                            }
                        });
                    });
                }

                //Pervies tab
                if (form.querySelectorAll(".previestab")){
                    Array.from(form.querySelectorAll(".previestab")).forEach(function (prevButton) {

                        prevButton.addEventListener("click", function () {
                            var prevTab = prevButton.getAttribute('data-previous');

                            document.getElementById(prevTab).removeAttribute('disabled');

                            var totalDone = prevButton.closest("form").querySelectorAll(".custom-nav .done").length;
                            for (var i = totalDone - 1; i < totalDone; i++) {
                                (prevButton.closest("form").querySelectorAll(".custom-nav .done")[i]) ? prevButton.closest("form").querySelectorAll(".custom-nav .done")[i].classList.remove('done'): '';
                            }
                            document.getElementById(prevTab).click();

                            document.getElementById(prevTab).setAttribute('disabled', 'disabled');
                        });
                    });
                }

                // Step number click
                var tabButtons = form.querySelectorAll('button[data-bs-toggle="pill"]');
                if (tabButtons){
                    Array.from(tabButtons).forEach(function (button, i) {
                        button.setAttribute("data-position", i);
                        button.addEventListener("click", function () {
                            form.classList.remove('was-validated');

                            var getProgressBar = button.getAttribute("data-progressbar");
                            if (getProgressBar) {
                                var totalLength = document.getElementById("custom-progress-bar").querySelectorAll("li").length - 1;
                                var current = i;
                                var percent = (current / totalLength) * 100;
                                document.getElementById("custom-progress-bar").querySelector('.progress-bar').style.width = percent + "%";
                            }
                            (form.querySelectorAll(".custom-nav .done").length > 0) ?
                            Array.from(form.querySelectorAll(".custom-nav .done")).forEach(function (doneTab) {
                                doneTab.classList.remove('done');
                            }): '';
                            for (var j = 0; j <= i; j++) {
                                tabButtons[j].classList.contains('active') ? tabButtons[j].classList.remove('done') : tabButtons[j].classList.add('done');
                            }
                        });
                    });
                }
            });
        }
    }

    function removeCheckedAttributeFromUsers() {
        // Get all elements with the class 'form-check-input-users'
        const userCheckboxes = document.querySelectorAll('.form-check-input-users');

        // Iterate over these elements and remove the 'checked' attribute
        userCheckboxes.forEach(function(checkbox) {
            checkbox.checked = false; // This effectively removes the 'checked' state
        });

    }

    /*
    function surveyCheckAllFormCheckInputs() {
        // Select all elements with the .form-check-input class
        var checkboxes = document.querySelectorAll('.form-check-input');

        // Iterate over them and add a change event listener to each one
        checkboxes.forEach(function(inputCheckbox) {
            // Add a change listener to the current checkbox
            inputCheckbox.addEventListener('change', function() {
                // This function is called whenever a checkbox is checked or unchecked
                // You can add your logic here for what happens when the state changes
                if (this.checked) {
                    this.setAttribute('checked', '');
                    //console.log(this.id + ' is checked');
                } else {
                    this.removeAttribute('checked');
                    //console.log(this.id + ' is unchecked');
                }

                if (this.classList.contains('form-check-input-companies')) {
                    var checkbox = this;
                    let companyId = checkbox.value;
                    let column = document.getElementById(`distributed-tab-company-${companyId}`);

                    column.style.display = 'none';
                    column.querySelectorAll('input').forEach(input => input.required = false);

                    if (checkbox.checked) {
                        column.style.display = '';
                        column.querySelectorAll('input').forEach(input => input.required = true);
                    }

                    // Remove all user checked to prevent to save hiden users id
                    removeCheckedAttributeFromUsers();
                }
            });
        });
    }
    */

    /*
    function surveyCheckAllFormCheckInputs(origin = null) {//
        // Select all elements with the .form-check-input class
        var checkboxes = document.querySelectorAll('.form-check-input');

        // Define a reusable action as a function
        function handleCheckboxChange(checkbox) {

            if (checkbox.classList.contains('form-check-input-companies')) {

                // Select all company checkboxes
                const companyCheckboxes = document.querySelectorAll('.form-check-input-companies');

                companyCheckboxes.forEach(function(checkbox) {
                    // Add a change event listener to each company checkbox
                    checkbox.addEventListener('change', function() {
                        // Use the checkbox value to identify the corresponding column
                        let companyId = this.value;
                        let column = document.getElementById(`distributed-tab-company-${companyId}`);

                        // Proceed only if the column is found
                        if (column) {
                            // Find all surveyor radio buttons within the identified column
                            const surveyorRadios = column.querySelectorAll('.form-check-input-users');

                            // Set the checked state of all surveyor radios based on the company checkbox's state
                            surveyorRadios.forEach(function(radio) {
                                // If the company checkbox is unchecked, uncheck the surveyor radio buttons
                                if (!checkbox.checked) {
                                    radio.checked = false;
                                }
                            });
                        }
                    });
                });
            }
        }

        checkboxes.forEach(function(inputCheckbox) {
            if(origin == 'survey'){
                // Immediately apply the logic to each checkbox
                handleCheckboxChange(inputCheckbox);
            }

            // Add a change listener to apply the logic upon future changes
            inputCheckbox.addEventListener('change', function() {
                handleCheckboxChange(this);
            });
        });
    }*/
    /*
    function surveyCheckAllFormCheckInputs(origin = null) {
        // This function is designed to be called with an optional 'origin' parameter.
        // It sets up event listeners on checkboxes to manage related radio buttons within the same 'company' group.

        // Attach change event listeners only to company checkboxes to avoid redundant event attachment.
        const companyCheckboxes = document.querySelectorAll('.form-check-input-companies');

        companyCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                // Use the checkbox value to identify the corresponding column
                let companyId = this.value;
                let column = document.getElementById(`distributed-tab-company-${companyId}`);

                // Proceed only if the column is found
                if (column) {
                    // Find all surveyor radio buttons within the identified column
                    const surveyorRadios = column.querySelectorAll('.form-check-input-users');

                    // Set the checked state of all surveyor radios based on the company checkbox's state
                    surveyorRadios.forEach(function(radio) {
                        // If the company checkbox is unchecked, uncheck the surveyor radio buttons
                        radio.checked = checkbox.checked;
                    });
                }
            });

            // If the function is called with 'origin' as 'survey', apply the initial state logic immediately.
            if (origin === 'survey') {
                let companyId = checkbox.value;
                let column = document.getElementById(`distributed-tab-company-${companyId}`);
                if (column) {
                    const surveyorRadios = column.querySelectorAll('.form-check-input-users');
                    surveyorRadios.forEach(function(radio) {
                        radio.checked = checkbox.checked;
                    });
                }
            }
        });
    }
    */
    function surveyCheckAllFormCheckInputs(origin = null) {
        // Attach change event listeners only to company checkboxes to avoid redundant event attachment.
        const companyCheckboxes = document.querySelectorAll('.form-check-input-companies');

        companyCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                // Use the checkbox value to identify the corresponding column
                let companyId = this.value;
                let column = document.getElementById(`distributed-tab-company-${companyId}`);

                // Proceed only if the column is found
                if (column) {
                    // Find all surveyor radio buttons within the identified column
                    const surveyorRadios = column.querySelectorAll('.form-check-input-users');

                    // Enable or disable all surveyor radios based on the company checkbox's state
                    surveyorRadios.forEach(function(radio) {
                        //radio.disabled = !checkbox.checked; // Disable if company is unchecked, enable if checked
                        if (!checkbox.checked) {
                            radio.checked = false; // Additionally uncheck if company is unchecked
                        }
                    });
                }
            });

            // Apply the initial state logic immediately if called with 'origin' as 'survey'.
            if (origin === 'survey') {
                let companyId = checkbox.value;
                let column = document.getElementById(`distributed-tab-company-${companyId}`);
                if (column) {
                    const surveyorRadios = column.querySelectorAll('.form-check-input-users');
                    surveyorRadios.forEach(function(radio) {
                        //radio.disabled = !checkbox.checked; // Disable if company is unchecked, enable if checked
                        radio.checked = checkbox.checked; // Synchronize checked state immediately
                    });
                }
            }

            // Prevent checking surveyor radio buttons if the corresponding company checkbox is not checked.
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('form-check-input-users')) {
                    const companyId = event.target.name.match(/\d+/)[0]; // Assuming the name attribute contains the company ID.
                    const companyCheckbox = document.querySelector(`.form-check-input-companies[value="${companyId}"]`);

                    if (companyCheckbox && !companyCheckbox.checked) {
                        console.log('Company not checked - preventing check');
                        event.preventDefault(); // Prevent the radio button from being checked.
                        return false; // For older browsers.
                    }
                }
            }, true);

        });
    }

    // Attach it to the window object to get from another file (similar export)
    //window.surveyCheckAllFormCheckInputs = surveyCheckAllFormCheckInputs;

    /*function surveyReloadUsersTab() {
        var loadUsersTabDiv = document.getElementById('load-users-tab');

        if (loadUsersTabDiv && surveyReloadUsersTabURL) {
            var surveyId = loadUsersTabDiv.getAttribute('data-survey-id') || ''; // Fallback to empty string if attribute not found

            var xhr = new XMLHttpRequest();
            var fullUrl = surveyId ? surveyReloadUsersTabURL + '/' + surveyId : surveyReloadUsersTabURL; // Append surveyId if present
            xhr.open('GET', fullUrl, true);
            xhr.setRequestHeader('Cache-Control', 'no-cache'); // Set the Cache-Control header to no-cache
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    loadUsersTabDiv.innerHTML = xhr.responseText;

                    setTimeout(() => {
                        surveyCheckAllFormCheckInputs();
                        console.log('surveyCheckAllFormCheckInputs');
                    }, 5000);

                }
            };
            xhr.send();
        }
    }*/
    async function surveyReloadUsersTab(origin = null) {
        const loadUsersTabDiv = document.getElementById('load-users-tab');

        if (loadUsersTabDiv && surveyReloadUsersTabURL) {
            const surveyId = loadUsersTabDiv.getAttribute('data-survey-id') || ''; // Fallback to empty string if attribute not found

            const fullUrl = surveyId ? `${surveyReloadUsersTabURL}/${surveyId}` : surveyReloadUsersTabURL;

            try {
                const response = await fetch(fullUrl, {
                    method: 'GET',
                    headers: {
                        'Cache-Control': 'no-cache',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const responseText = await response.text();

                    if(responseText){
                        loadUsersTabDiv.innerHTML = responseText;

                        surveyCheckAllFormCheckInputs(origin);

                        bsPopoverTooltip();
                    }
                } else {
                    // Handle HTTP error (response.ok is false)
                    throw new Error('Network response was not ok.');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
    }
    // Attach it to the window object to get from another file (similar export)
    window.surveyReloadUsersTab = surveyReloadUsersTab;


    // Attach event listeners for the modal form
    function attachModalEventListeners() {
        // store/update surveysForm
        var btnStoreOrUpdate = document.getElementById('btn-surveys-store-or-update');
        if(btnStoreOrUpdate){
            btnStoreOrUpdate.addEventListener('click', async function(event) {
                event.preventDefault();

                const form = document.getElementById('surveysForm');
                if (!form) {
                    console.error('Form not found');
                    return;
                }

                // Validate ID
                const surveyId = form.querySelector('input[name="id"]').value;

                const formData = new FormData(form);
                console.log(formData);
                //return;

                // START Check if the number of selected companies and users:
                // Check if the number of selected companies (checked checkboxes with the name companies[]) matches the quantity of selected surveyors (radio buttons grouped by company, e.g., surveyor[1])
                const checkedCompanies = form.querySelectorAll('.form-check-input-companies:checked');

                let isValid = true;

                checkedCompanies.forEach(companyCheckbox => {
                    const companyId = companyCheckbox.value;
                    // Count the number of selected surveyors for this company
                    const selectedSurveyors = form.querySelectorAll(`input[name="surveyor[${companyId}]"]:checked`).length;

                    // If no surveyor is selected for a checked company, fail the validation
                    if (selectedSurveyors === 0) {
                        isValid = false;
                        // console.error(`No surveyor selected for company ${companyId}`);

                        toastAlert(`Necessário selecionar ao menos um Usuário para a unidade ${companyId}`, 'danger', 10000);
                    }
                });
                if (!isValid) {
                    return;
                }
                // END Check if the number of selected companies and users

                // Transform data
                var data = {};
                // Iterate over formData entries
                for (let [key, value] of formData.entries()) {
                    // Check if the key includes 'surveyor'
                    if (key.startsWith('surveyor')) {
                        // Extract the index - assuming the format is 'surveyor[index]'
                        let index = key.match(/\[(\d+)\]/)[1]; // Get the number inside brackets

                        // Initialize the array if it doesn't exist
                        if (!data.surveyor) {
                            data.surveyor = [];
                        }

                        // Push the object with id as index and user_id as value
                        data.surveyor.push({ company_id: index, user_id: value });
                    }

                    if (key.startsWith('auditor')) {
                        // Extract the index - assuming the format is 'surveyor[index]'
                        let index = key.match(/\[(\d+)\]/)[1]; // Get the number inside brackets

                        // Initialize the array if it doesn't exist
                        if (!data.auditor) {
                            data.auditor = [];
                        }

                        // Push the object with id as index and user_id as value
                        data.auditor.push({ company_id: index, user_id: value });
                    }
                }
                //console.log(data);
                //console.log(JSON.stringify(data, null, 2));
                //return;

                //formData.append('distributed_data', JSON.stringify(transformedData, null, 2));
                formData.append('distributed_data', JSON.stringify(data, null, 2));

                showPreloader();
                try {
                    let url = surveyId ? surveysStoreOrUpdateURL + `/${surveyId}` : surveysStoreOrUpdateURL;

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
                        //toastAlert(data.message, 'success', 10000);

                        document.querySelector('input[name="id"]').value = data.id;

                        sweetWizardAlert(data.message, surveysIndexURL, 'success', 'Continuar Editando', 'Concluir');
                    } else {
                        toastAlert(data.message, 'danger', 60000);
                    }

                    showPreloader(false);
                } catch (error) {
                    //console.error('Error:', error);

                    showPreloader(false);

                    toastAlert('Error: ' + error, 'danger', 60000);
                }
            });
        }
    }

    var loadlistingAssignmentActivities = document.getElementById('load-assignment-activities');
    if( loadlistingAssignmentActivities && listingAssignmentActivitiesURL ){
        function listingAssignmentActivities() {
            var subDays = loadlistingAssignmentActivities.getAttribute("data-subDays") ?? 1;

            var btnShowAll = document.getElementById('btn-show-all-assignments');

            fetch(listingAssignmentActivitiesURL + '/' + subDays, {
                    method: 'GET',
                    headers: {
                        'Cache-Control': 'no-cache',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    //console.log(JSON.stringify(activities, null, 2));

                    const container = loadlistingAssignmentActivities;

                    container.innerHTML = '';

                    if(data.success && data.activities){
                        data.activities.forEach(activity => {
                            const activityElement = document.createElement('div');
                            activityElement.className = 'card border border-dashed shadow-none mt-3 mb-0 bg-body';
                            activityElement.innerHTML = `
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs flex-shrink-0 me-2">
                                            <a href="${activity.designatedUserProfileURL}" class="text-body d-block" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Visualizar todas as Tarefas delegadas a ${activity.designatedUserName}">
                                                <img src="${activity.designatedUserAvatar}" alt="avatar" class="img-fluid rounded-circle">
                                            </a>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fs-11 mb-0 fw-bold">
                                                ${activity.designatedUserName}

                                                <div class="fs-11 mb-0 text-info
                                                text-opacity-75 fw-bold">${activity.companyName}</div>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 text-end">
                                            ${activity.label}

                                            <div class="fs-10 mb-0 text-muted">${activity.createddAt}</div>
                                        </div>
                                    </div>

                                    <div class="fs-12 mb-0 text-muted mb-1 mt-1">${activity.surveyTitle}</div>

                                    <div class="progress progress-sm mt-1 custom-progress" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="${activity.percentage}%">
                                        <div class="progress-bar bg-${activity.progressBarClass}" role="progressbar" style="width: ${activity.percentage}%" aria-valuenow="${activity.percentage}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            `;
                            container.appendChild(activityElement);
                        });

                        bsPopoverTooltip();

                        if(btnShowAll){
                            btnShowAll.style.display = "inline-flex";
                        }
                    }else{
                        container.innerHTML = '<div class="text-center text-muted mt-3">'+ data.message +'</div>';

                        if(btnShowAll){
                            btnShowAll.style.display = "none";
                        }
                    }

                    return;
                })
                .catch(error => console.error('Error:', error)
            );
        }

        listingAssignmentActivities();
        setInterval(function () {
            listingAssignmentActivities();
        }, 60000);// 60000 = 1 minute
    }

    var loadAssignmentListing = document.getElementById('load-assignment-listing');
    if( loadAssignmentListing && listingAssignmentByIdURL ){
        const buttonsAssignmentListing = document.querySelectorAll('.btn-assignment-listing');
        if(buttonsAssignmentListing){
            buttonsAssignmentListing.forEach(function(button) {
                button.addEventListener('click', async function(event) {
                    event.preventDefault();

                    showPreloader();

                    const surveyId = this.getAttribute('data-survey-id');
                    const surveyTitle = this.getAttribute('data-survey-title');
                    const url = listingAssignmentByIdURL + '/' + surveyId;

                    try {
                        const response = await fetch(url, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }

                        const html = await response.text();

                        // Assuming you have a modal with ID 'assignmentsListingModal'
                        const modalElement = document.getElementById('assignmentsListingModal');
                        const modalTitle = modalElement.querySelector('.modal-title');
                        const modalBody = modalElement.querySelector('.modal-body');

                        modalTitle.innerHTML = 'Listagem de Tarefas :: ' + ' <span class="text-theme">' + surveyTitle + '</span>';
                        modalBody.innerHTML = html;

                        const modal = new bootstrap.Modal(modalElement);
                        modal.show();
                    } catch (error) {
                        console.error('Error:', error);
                    }

                    showPreloader(false);
                });
            });
        }
    }


    const swapButton = document.getElementById('btn-surveys-swap-toggle');
    if(swapButton){
        swapButton.addEventListener('click', async function(event) {
            event.preventDefault;

            showPreloader();

            if(getCookie('surveys-swap')){
                removeCookie('surveys-swap');
            }else{
                setCookie('surveys-swap', true, 2);
            }

            location.reload(true);
        });
    }


    // Make the preview request
    if(document.getElementById('surveysForm')){
        var idInput = document.querySelector('input[name="id"]');
        if(idInput){
            var idValue = idInput ? idInput.value : null;
            ajaxContentFromURL(idValue, surveysShowURL);
        }
    }

    // Call the function when the DOM is fully loaded
    initFlatpickr();
    maxLengthTextarea();
    layouRightSide();
    toggleTableRows();
   // choicesListeners(surveysTermsSearchURL, surveysStoreOrUpdateURL, choicesSelectorClass);

});
