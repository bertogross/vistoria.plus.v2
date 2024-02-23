import {
    toastAlert,
    toggleTableRows,
    monthsInPortuguese,
    bsPopoverTooltip
} from './helpers.js';

// Event listeners setup
document.addEventListener('DOMContentLoaded', function() {
    // A flag to track whether the execution is currently in progress or not.
    let isExecutionInProgress = false;

    // Prevent right-click context menu.
    function preventRightClick(event) {
        event.preventDefault();
    }

    // Update the progress bar's color based on the given percentage.
    function updateProgressBarColor(percent) {
        const progressBar = document.querySelector('.progress-bar');
        progressBar.classList.remove('bg-warning-subtle', 'bg-warning', 'bg-info', 'bg-theme');
        //console.log('progress: ' + percent);

        if (percent < 10) {
            progressBar.classList.add('bg-warning-subtle');
        } else if (percent >= 10 && percent < 25) {
            progressBar.classList.add('bg-warning');
        } else if (percent >= 25 && percent < 75) {
            progressBar.classList.add('bg-info');
        } else if (percent >= 75 && percent < 100) {
            progressBar.classList.add('bg-primary');
        } else {
            progressBar.classList.add('bg-theme');
        }
    }

    // Increment the month of a given date string in "YYYY-mm" format.
    function incrementMonth(dateStr) {
        const [year, month] = dateStr.split('-').map(Number);
        return month === 12 ? `${year + 1}-01` : `${year}-${String(month + 1).padStart(2, '0')}`;
    }

    // Calculate the month difference between two dates in "YYYY-mm" format.
    function monthDifference(startDate, endDate) {
        const [startYear, startMonth] = startDate.split('-').map(Number);
        const [endYear, endMonth] = endDate.split('-').map(Number);
        return (endYear - startYear) * 12 + endMonth - startMonth;
    }

    // Sends a request to the specified URL and returns the JSON response.
    async function sendRequest(url) {
        const response = await fetch(url);
        if (!response.ok) {
            toastAlert(Error(response.statusText), 'danger', 10000);
            throw new Error(response.statusText);
        }
        return response.json();
    }

    // Recursively makes requests to the API and updates the UI accordingly.
    async function makeRequests(meantime, initialMeantime, completedIterations) {
        // UI adjustments for synchronization progress
        document.getElementById('synchronization-progress').classList.remove('d-none');
        document.getElementById('btn-start-synchronization').classList.add('d-none');
        toggleCustomBackdrop(true);
        isExecutionInProgress = true;
        document.addEventListener('contextmenu', preventRightClick);

        try {
            // Sleep for X miliseconds
            let ms = 2000;
            await new Promise(resolve => setTimeout(resolve, ms));

            //document.querySelector('.synchronization-percent-text').innerHTML = `Sincronização <span class="text-theme">${convertMeantimeToPortuguese(meantime)}</span> em andamento<br><br><small class="text-warning">A importação poderá levar algum tempo. Não feche o navegador e nem atualize a página até que o processo seja concluído.</small>`;
            document.querySelector('.synchronization-percent-text').innerHTML = `Sincronização <span class="text-theme">${convertMeantimeToPortuguese(meantime)}</span> em andamento<br><br><small class="text-warning">Não feche o navegador e nem atualize a página até que o processo seja concluído.</small>`;

            const response = await sendRequest(updateSalesFromSysmoURL + '/' + meantime);
            //console.log(response);
            if (!response || ( response.success === false && !response.motive) ) {
                finalizeSynchronization('error', response.message, '0%');

                return;
            }

            // Progress calculations and UI updates
            const totalMonths = monthDifference(initialMeantime, new Date().toISOString().slice(0, 7));
            const elapsedMonths = monthDifference(initialMeantime, meantime);
            const percent = (elapsedMonths / totalMonths) * 100;

            document.querySelector('.progress-bar').style.width = `${percent.toFixed(0)}%`;
            document.querySelector('.synchronization-percent').innerHTML = `${percent.toFixed(0)}%`;

            updateProgressBarColor(percent);

            // Populate the <li> elements with concluded meantimes
            const ulElement = document.querySelector('.concluded-meantimes');
            const liElement = document.createElement('li');
            if(response.success === false && response.motive == 'noData'){
                toastAlert(response.message, 'warning', 10000);

                liElement.innerHTML = `<span data-bs-toggle="tooltip" data-bs-placement="top" title="${response.message}"><i class="ri-error-warning-fill text-warning align-bottom me-2"></i>${convertMeantimeToPortuguese(meantime)}</span>`;
            }else{
                toastAlert(response.message, 'success', 10000);

                liElement.innerHTML = `<span data-bs-toggle="tooltip" data-bs-placement="top" title="Dados ${convertMeantimeToPortuguese(meantime)} recebidos"><i class="ri-checkbox-circle-fill text-success align-bottom me-2"></i>${convertMeantimeToPortuguese(meantime)}</span>`;
            }

            bsPopoverTooltip();

            // Insert the new li element at the beginning of the ul element
            ulElement.insertBefore(liElement, ulElement.firstChild);

            // Determine next steps based on progress
            const nextMeantime = incrementMonth(meantime);
            if (new Date(nextMeantime) <= new Date()) {
                makeRequests(nextMeantime, initialMeantime, completedIterations + 1);
            } else {
                finalizeSynchronization('success', 'Concluído', '100%');
            }
        } catch (error) {
            toggleCustomBackdrop(false);

            document.querySelector('.synchronization-time').innerHTML = '';

            isExecutionInProgress = false;

            document.removeEventListener('contextmenu', preventRightClick);

            toastAlert(`Error: ${error.message}`, 'danger', 10000);

            document.querySelector('.synchronization-percent-text').innerHTML = '<span class="text-danger">Erro: ' + error.message + '</span>';
        }
    }

    // Showing and hiding the custom backdrop based on a boolean parameter
    function toggleCustomBackdrop(show) {
        var customBackdrop = document.getElementById('custom-backdrop');
        if (customBackdrop) {
            if (show) {
                customBackdrop.classList.remove('d-none');
                customBackdrop.classList.add('d-block');
            } else {
                customBackdrop.classList.remove('d-block');
                customBackdrop.classList.add('d-none');
            }
        } else {
            console.error("Element with id 'custom-backdrop' not found");
        }
    }

    // Finalize the synchronization process and update the UI.
    function finalizeSynchronization(type, message, percentage) {
        setTimeout(function () {
            document.querySelector('.progress-bar').style.width = percentage;
            document.querySelector('.synchronization-percent-text').innerHTML = message;
            document.querySelector('.synchronization-time').innerHTML = '';

            isExecutionInProgress = false;
            document.removeEventListener('contextmenu', preventRightClick);

            toggleCustomBackdrop(false);
            toastAlert(message, type, 100000000);
        }, 5000);
    }

    // Convert 'YYYY-mm' format to human-readable Portuguese format
    function convertMeantimeToPortuguese(meantime) {
        const [year, month] = meantime.split('-');

        return `${monthsInPortuguese[month - 1]} de ${year}`;
    }

    // Warn the user if they try to leave the page during execution
    window.addEventListener('beforeunload', function(event) {
        if (isExecutionInProgress) {
            const message = 'A execução está em andamento. Tem certeza de que quer sair?';
            event.returnValue = message;
            return message;
        }
    });

    // Event listener for the start synchronization button
    document.getElementById('btn-start-synchronization').addEventListener('click', function(event) {
        event.preventDefault();

        // Display initial status messages with delays
        const estimatedElement = document.querySelector('.synchronization-time');
        ['Conectando...', 'Aguardando resposta...', 'Requisitando dados...'].forEach((message, index) => {
            setTimeout(() => {
                estimatedElement.innerHTML = `<span class="blink">${message}</span>`;
            }, (index * 3000) + 3000);
        });

        // Prepare year and month options for user selection
        const currentYear = new Date().getFullYear();
        const monthNames = monthsInPortuguese;

        const yearOptions = Array.from({ length: 6 }, (_, i) => currentYear - 5 + i)
            .map(year => `<option value="${year}" ${year === currentYear ? 'selected' : ''}>${year}</option>`)
            .join('');

        const monthOptions = monthNames.map((month, index) =>
            `<option value="${index + 1}" ${index + 1 === new Date().getMonth() + 1 ? 'selected' : ''}>${month}</option>`)
            .join('');

        // Display the month and year selection dialog using SweetAlert2
        Swal.fire({
            title: 'Defina o Mês e Ano de início',
            html: `
                <select id="month" class="form-select mb-2">
                    ${monthOptions}
                </select>
                <select id="year" class="form-control form-select">
                    ${yearOptions}
                </select>
            `,
            focusConfirm: false,
            buttonsStyling: false,
            showCloseButton: true,
            showCancelButton: true,
            cancelButtonText: 'Voltar',
            confirmButtonText: 'Prosseguir',
            customClass: {
                cancelButton: 'btn btn-sm btn-outline-warning ms-1',
                closeButton: 'btn btn-dark ms-1',
                confirmButton: 'btn btn-theme me-1'
            },
            preConfirm: function() {
                const year = document.getElementById('year').value;
                const month = document.getElementById('month').value;
                const fromMeantime = `${year}-${month.padStart(2, '0')}`;

                makeRequests(fromMeantime, fromMeantime, 0);
            }
        });
    });

    // Call the function when the DOM is fully loaded
    toggleTableRows();

});

