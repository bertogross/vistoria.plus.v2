
// Toast Notifications
// Display a toast notification using Bootstrap's Toast API with a backdrop
export function toastAlert(message, type = 'success', duration = 3000, backdrop = false) {
    //setTimeout(() => {
        // Define the HTML template for the toast
        const icon = type === 'success' ? 'ri-checkbox-circle-fill text-success' : 'ri-alert-fill text-' + type;
        type = type === 'error' ? 'danger' : type;

        let toastContainerElement = document.querySelectorAll('.toast-container');
        const toastBackdrop = `<div class="toast-backdrop"></div>`;

        // Remove existing toast containers
        if (toastContainerElement) {
            toastContainerElement.forEach(element => element.remove());
        }

        // Remove existing toast backdrops
        let toastBackdropElement = document.querySelectorAll('.toast-backdrop');
        if (toastBackdropElement) {
            toastBackdropElement.forEach(element => element.remove());
        }

        const ToastHtml = `
            <div class="toast-container position-fixed bottom-0 end-0 p-3">
                <div class="toast fade show toast-border-${type} overflow-hidden mt-3" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-2">
                                <i class="${icon} align-middle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <button type="button" class="btn-close btn-close-white me-2 m-auto float-end fs-10" data-bs-dismiss="toast" aria-label="Close"></button>
                                <h6 class="mb-0">${message}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add the toast and backdrop to the end of the document body
        if (type != 'success' && backdrop) {
            document.body.insertAdjacentHTML('beforeend', toastBackdrop);
        }
        document.body.insertAdjacentHTML('beforeend', ToastHtml);

        // Initialize and show the toast using Bootstrap's API
        const toastElement = document.querySelector('.toast-container .toast');
        const toast = new bootstrap.Toast(toastElement, { autohide: false });
        toast.show();

        // Add event listener to the close button
        const closeButton = document.querySelector('.btn-close');
        closeButton.addEventListener('click', () => {
            toast.hide();

            toastContainerElement = document.querySelectorAll('.toast-container');
            if (toastContainerElement) {
                toastContainerElement.forEach(element => element.remove());
            }

            toastBackdropElement = document.querySelectorAll('.toast-backdrop');
            if (toastBackdropElement) {
                toastBackdropElement.forEach(element => element.remove());
            }
        });

        // If a duration is provided, hide the toast after the duration
        setTimeout(() => {
            toast.hide();

            // Remove the toast container and backdrop once the toast is completely hidden
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastContainerElement = document.querySelectorAll('.toast-container');
                if(toastContainerElement){
                    toastContainerElement.forEach(element => element.remove());
                }

                toastBackdropElement = document.querySelectorAll('.toast-backdrop');
                if(toastBackdropElement){
                    toastBackdropElement.forEach(element => element.remove());
                }
            });
        }, duration);

    //}, 100);
}

export function sweetWizardAlert(message, urlToRedirect = false, icon = 'success', cancelButtonText = 'Continuar Editando', confirmButtonText = 'Prosseguir', Trigger){
    Swal.fire({
        title: message,
        icon: icon,
        buttonsStyling: false,
        confirmButtonText: confirmButtonText,
            confirmButtonClass: 'btn btn-outline-theme w-xs me-2',
        cancelButtonText: cancelButtonText,
            cancelButtonClass: 'btn btn-sm btn-outline-info w-xs',
                showCancelButton: true,
        denyButtonText: 'Não',
            denyButtonClass: 'btn btn-sm btn-danger w-xs me-2',
                showDenyButton: false,
        showCloseButton: false,
        allowOutsideClick: false
    }).then(function (result) {
        /* Read more about isConfirmed, isDenied below */

        var btnTrigger = document.querySelector(''+Trigger+'');

        if (result.isConfirmed) {
            var timerInterval;
            Swal.fire({
                title: 'Redirecionando...',
                html: '',
                timer: 2000,
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
                    if(Trigger && btnTrigger){
                        btnTrigger.click();
                    }

                    setTimeout(() => {
                        if(urlToRedirect){
                            window.location.href = urlToRedirect;
                        }
                    }, 100);
                }
            });
        }
    })
}

export function showPreloader(show = true) {
    var preloader = document.getElementById("preloader");
    if (preloader) {
        preloader.style.opacity = show ? "0.5" : "0";
        preloader.style.visibility = show ? "visible" : "hidden";
    }

    if(!show){
        preloader.style.visibility = "hidden";
    }

    setTimeout(function () {
        preloader.style.visibility = "hidden";
    }, 60000);
}

export function printThis(){
    // https://medium.com/@sewwandikaus.13/export-content-to-pdf-using-javascript-and-html2pdf-3a6510cd39c6
    // https://www.npmjs.com/package/html2pdf.js/v/0.9.0
    // https://ekoopmans.github.io/html2pdf.js/
    // https://rawgit.com/MrRio/jsPDF/master/docs/jsPDF.html
    const btnPrintThis = document.querySelectorAll('.btn-print-this');
    if(btnPrintThis){
        btnPrintThis.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();

                const elementId = this.getAttribute("data-target-id");
                const fileName = this.getAttribute("data-pdf-name");

                toastAlert('Gerando PDF...', 'info', 5000, true);

                var element = document.getElementById(elementId);
                var options = {
                    margin: 0.9,
                    filename: fileName + '.pdf',
                    image: {
                        type: 'jpeg',
                        quality: 0.98
                    },
                    //html2canvas: { scale: 2 },
                    jsPDF: {
                        unit: 'mm',
                        format: 'a2',
                        orientation: 'portrait' // portrait landscape
                    }
                };
                html2pdf(element, options);
            });
        });
    }
}


// Multiple Modals
// Maintain modal-open when close another modal
export function multipleModal() {
    function destroyModal() {
        if(document.querySelectorAll('.modal .btn-destroy').length){
            document.querySelectorAll('.modal .btn-destroy').forEach(function (btnClose) {
                btnClose.addEventListener('click', function () {
                    var modalElement = this.closest('.modal');
                    if (modalElement) {
                        modalElement.remove();
                    }
                });
            });
        }
    }
    setTimeout(function () {
        document.querySelectorAll('.modal').forEach(function (modal) {
            modal.addEventListener('show', function () {
                document.body.classList.add('modal-open');
            });

            modal.addEventListener('hidden', function () {
                document.body.classList.remove('modal-open');
            });
        });

        // Multiple modals overlay
        document.addEventListener('show.bs.modal', function (event) {
            var modal = event.target;
            var modals = Array.from(document.querySelectorAll('.modal')).filter(function (modal) {
                return window.getComputedStyle(modal).display !== 'none';
            });
            var zIndex = 1050 + 10 * modals.length;
            modal.style.zIndex = zIndex;

            var backdrops = document.querySelectorAll('.modal-backdrop:not(.modal-stack)');
            backdrops.forEach(function (backdrop) {
                backdrop.style.zIndex = zIndex - 1;
                backdrop.classList.add('modal-stack');
            });
        });

        destroyModal();
    }, 500);

}

/*
export function preventWizardNavigation(){
    // Using plain JavaScript to disable click on nav items
    document.querySelectorAll('.nav-item .nav-link').forEach(function(tab) {
        tab.addEventListener('click', function(event) {
            if (!this.classList.contains('active')) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }
        });
    });
}
*/

export function formatNumberInput(selector = '.format-numbers', decimals = 0) {
    const numberInputs = document.querySelectorAll(selector);

    function formatValue(value, decimals) {
        value = value.replace(/[^\d,]/g, ''); // Remove non-numeric characters except comma

        if (!value) {
            return '';
        }

        value = value.replace(',', '.'); // Replace comma with dot for parseFloat

        let number = parseFloat(value);

        if (isNaN(number)) {
            return '';
        }

        if (decimals > 0) {
            return number.toLocaleString('pt-BR', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
        } else {
            return number.toLocaleString('pt-BR');
        }
    }

    numberInputs.forEach(input => {
        // Format value when typing
        input.addEventListener('input', function(event) {
            if (event.inputType === "deleteContentBackward") {
                return; // If backspace was pressed, just return
            }

            var target = event.target;
            target.value = formatValue(target.value, decimals);
        });

        // Format value on page load
        input.value = formatValue(input.value, decimals);
    });
}

export function getChartColorsArray(chartId) {
    if (document.getElementById(chartId) !== null) {
      var colors = document.getElementById(chartId).getAttribute("data-colors");

      if (colors) {
        colors = JSON.parse(colors);
        return colors.map(function (value) {
          var newValue = value.replace(" ", "");
          if (newValue.indexOf(",") === -1) {
            var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
            if (color) return color;
            else return newValue;
          } else {
            var val = value.split(',');
            if (val.length == 2) {
              var rgbaColor = getComputedStyle(document.documentElement).getPropertyValue(val[0]);
              rgbaColor = "rgba(" + rgbaColor + "," + val[1] + ")";
              return rgbaColor;
            } else {
              return newValue;
            }
          }
        });
      } else {
        console.warn('data-colors Attribute not found on:', chartId);
      }
    }
}

export function onlyNumbers(number){
    if (number === null || number === undefined) {
        return 0;
    }
    var result = number.toString().replace(/\D/g, '');
    return parseInt(result);
}

export function formatNumber(number, decimalPlaces = 0){
    number = number ? parseFloat(number.replace(',', '.')) : null;

    if(number){
        return Number(number).toLocaleString('pt-BR', { minimumFractionDigits: decimalPlaces, maximumFractionDigits: decimalPlaces });
    }
    return null;
}

export function sumInputNumbers(from, to, decimal = 0) {
    const inputs = document.querySelectorAll(from);
    const resultDiv = document.querySelector(to);

    if (!inputs) {
        console.error(`Element with Selector "${from}" not found`);
        return;
    }

    if (!resultDiv) {
        console.error(`Element with Selector "${to}" not found`);
        return;
    }

    function updateSum() {
        let sum = 0;
        inputs.forEach(input => {
            const value = onlyNumbers(input.value) || 0;

            sum += value;
        });
        const formatter = new Intl.NumberFormat('pt-BR', {
            minimumFractionDigits: decimal,
            maximumFractionDigits: decimal,
        });
        resultDiv.textContent = formatter.format(sum);

    }

    inputs.forEach(input => {
        input.addEventListener('input', updateSum);
    });

    updateSum();
}

export function setCookie(cname, cvalue, exdays = 1) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();

    var cookieString = cname + "=" + cvalue + ";" + expires + ";path=/;SameSite=Strict;Secure";

    document.cookie = cookieString;
}

export function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
};

export function removeCookie(cname) {
    var expires = "expires=Thu, 01 Jan 1970 00:00:00 UTC";
    var cookieString = cname + "=;" + expires + ";path=/;SameSite=Strict;Secure";
    document.cookie = cookieString;
    // PS: Cookies with the HttpOnly = true flag cannot be deleted using JavaScript
}


// Format file size
export function formatSize(bytes) {
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes === 0) return '0 Byte';
    const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}

// Set a value in the session storage
export function setSessionStorage(storageName, value = true) {
    if(value){
        sessionStorage.setItem(storageName, value);
    }else{
        sessionStorage.removeItem(storageName);
    }
}

// Get a value from the session storage.
export function getSessionStorage(storageName) {
    return sessionStorage.getItem(storageName);
}

export function toggleZoomInOut() {
    var zoomTarget = document.querySelector('.toogle_zoomInOut');

    if (zoomTarget) {
        // Set initial zoom level
        var zoomLevel = getSessionStorage('toggle-zoom') || 100;
        zoomLevel = Number(zoomLevel);

        if (zoomLevel < 100 || zoomLevel > 100) {
            zoomTarget.style.transform = 'scale(' + (zoomLevel / 100) + ')';
            zoomTarget.style.transformOrigin = '50% 0px 0px';
            zoomTarget.style.width = '100%';
        }

        // Click events
        document.addEventListener('click', function (event) {
            if (event.target.id === 'zoom_in') {
                event.preventDefault();
                zoomPage(10, event.target, zoomTarget);
            } else if (event.target.id === 'zoom_out') {
                event.preventDefault();
                zoomPage(-10, event.target, zoomTarget);
            } else if (event.target.id === 'zoom_reset') {
                event.preventDefault();
                zoomPage(0, event.target, zoomTarget);
            }
        });

        // Zoom function
        function zoomPage(step, trigger, target) {
            // Zoom just to steps in or out
            if (zoomLevel >= 120 && step > 0 || zoomLevel <= 80 && step < 0) return;

            // Set / reset zoom
            if (step === 0) zoomLevel = 100;
            else zoomLevel = zoomLevel + step;

            // Set page zoom via CSS
            target.style.transform = 'scale(' + (zoomLevel / 100) + ')';
            target.style.transformOrigin = '50% 0';

            // Adjust page to zoom width
            if (zoomLevel > 100) target.style.width = (zoomLevel * 1.2) + '%';
            else target.style.width = '100%';

            document.getElementById('zoom_reset').value = zoomLevel + '%';

            setSessionStorage('toggle-zoom', zoomLevel);

            // Activate / deactivate trigger (use CSS to make them look different)
            if (zoomLevel >= 120 || zoomLevel <= 80) trigger.classList.add('disabled');
            else Array.from(document.querySelectorAll('ul .disabled')).forEach(function (el) {
                el.classList.remove('disabled');
            });

            if (zoomLevel !== 100) document.getElementById('zoom_reset').classList.remove('disabled');
            else document.getElementById('zoom_reset').classList.add('disabled');
        }
    }
}

// Adds event listeners to show a button when an input field in a form changes.
export function showButtonWhenInputChange() {
    // Helper function to handle showing the button and hiding other elements.
    function handleInputChange(form) {
        // Show the button
        var wrapFormBtn = form.querySelector('.wrap-form-btn');
        if (wrapFormBtn) {
            wrapFormBtn.classList.remove('d-none');
        }

        // Hide the listing and footer if not in a modal
        if (!document.body.classList.contains('modal-open')) {
            var loadListing = document.getElementById('load-listing');
            //Hide the load listing element slowly on form change.
            if (loadListing) {
                loadListing.classList.add("hide-slowly");
            }
        }
    }

    // Add event listener for change events
    document.addEventListener('change', function (event) {
        if (event.target.closest('form')) {
            handleInputChange(event.target.closest('form'));
        }
    });

    // Add event listener for keyup events
    document.addEventListener('keyup', function (event) {
        if (event.target.closest('form')) {
            handleInputChange(event.target.closest('form'));
        }
    });
}

// Anchor
// https://developer.mozilla.org/pt-BR/docs/Web/API/Element/scrollIntoView
export function goTo(id, top = 150, block = 'start') {//start, end
    let element = document.getElementById(id);

    if (element !== null) {
        element.scrollIntoView({ behavior: 'smooth', top: top, block: block, inline: 'nearest' });
    }
}

// Add percent
export function percentageResult(price, percentage, decimal = 0){
    var result = '';

    var percent = percentage ? Number((percentage/100)) : '';
    //console.log(percent);

    var value = price ? Number((price/100)) : '';
    //console.log(value);

    result = value && percent ? ( value + ( Number(percent/100) * value ) ).toFixed(decimal) : '';
    //console.log(result);

    return result;
}

// Function to update the progress bar
export function updateProgressBar(number1, number2, progressBarId) {
    // Calculate the percentage
    const percentage = (number1 / number2) * 100;

    // Update the progress bar width and label
    const progressBarContainer = document.getElementById(progressBarId);
    if(progressBarContainer){
        const progressBar = progressBarContainer.querySelector('.progress-bar');
        const progressLabel = progressBarContainer.querySelector('.label');

        progressBar.style.width = percentage + '%';
        progressLabel.innerText = Math.round(percentage) + '%';

        // Remove all background color classes
        progressBar.classList.remove('bg-success', 'bg-info', 'bg-primary', 'bg-warning', 'bg-danger');

        // Change background color based on the percentage
        if (percentage >= 100) {
            progressBar.classList.add('bg-success'); // Completed
        } else if (percentage >= 75) {
            progressBar.classList.add('bg-info'); // High progress
        } else if (percentage >= 50) {
            progressBar.classList.add('bg-primary'); // Moderate progress
        } else if (percentage >= 25) {
            progressBar.classList.add('bg-warning'); // Low progress
        } else {
            progressBar.classList.add('bg-danger'); // Just started or no progress
        }
    }
}

export function bsPopoverTooltip() {
    setTimeout(() => {
        // Arrays to keep track of all tooltips and popovers
        let allTooltips = [];
        let allPopovers = [];

        // Function to hide all tooltips
        function hideAllTooltips() {
            allTooltips.forEach(tooltip => tooltip.hide());
            allTooltips = []; // Clear the array after hiding all tooltips
        }

        // Function to hide all popovers
        function hideAllPopovers() {
            allPopovers.forEach(popover => popover.hide());
            allPopovers = []; // Clear the array after hiding all popovers
        }

        // Hide existing tooltips and popovers
        hideAllTooltips();
        hideAllPopovers();

        // Find all elements with data-bs-toggle
        var toggles = document.querySelectorAll('[data-bs-toggle]');

        toggles.forEach(function(toggle) {
            var toggleType = toggle.getAttribute('data-bs-toggle');
            if (toggleType === 'tooltip') {
                // Initialize tooltip and store the instance
                allTooltips.push(new bootstrap.Tooltip(toggle));
            } else if (toggleType === 'popover') {
                // Initialize popover and store the instance
                allPopovers.push(new bootstrap.Popover(toggle));
            }
        });
    }, 100);
}

export function initFlatpickr() {
    const elements = document.querySelectorAll('.flatpickr-default');
    if(elements){
        elements.forEach(element => {
            var currentValue = element.value ? element.value : null;

            flatpickr(element, {
                dateFormat: "d/m/Y",
                locale: "pt",
                allowInput: true,
                clear: true,
                minDate: "today",
                defaultDate: currentValue,
                maxDate: new Date().fp_incr(1100)// Set the maximum date to 360 days from today
            });
        });
    }

    const elementsBetween = document.querySelectorAll('.flatpickr-between');
    if(elementsBetween){
        var elementStart = document.getElementById('date-recurring-start');
        var elementEnd = document.getElementById('date-recurring-end');

        if(elementStart && elementEnd){
            var startValue = elementStart.value ? elementStart.value : null;
            var endValue = elementEnd.value ? elementEnd.value : null;

            // Initialize Flatpickr for the end date
            const endDatePicker = flatpickr("#date-recurring-end", {
                dateFormat: "d/m/Y",
                locale: "pt",
                allowInput: true,
                clear: true,
                minDate: "today",
                defaultDate: endValue,
                maxDate: new Date().fp_incr(3300)// Set the maximum date to 3300 days from today
            });

            // Initialize Flatpickr for the start date
            flatpickr("#date-recurring-start", {
                dateFormat: "d/m/Y",
                locale: "pt",
                allowInput: true,
                clear: true,
                minDate: "today",
                defaultDate: startValue,
                maxDate: new Date().fp_incr(1100),// Set the maximum date to 1100 days from today
                onChange: function(selectedDates) {
                    // Update the minDate for endDatePicker
                    const startDate = selectedDates[0];
                    endDatePicker.set('minDate', startDate);
                }
            });
        }
    }

    const elementsRange = document.querySelectorAll('.flatpickr-range');
    if(elementsRange){
        elementsRange.forEach(element => {
            var getMinDate = element.getAttribute("data-min-date");
            getMinDate = !getMinDate ? 'today' : getMinDate;

            var getMaxDate = element.getAttribute("data-max-date");
            getMaxDate = !getMaxDate ? 'today' : getMaxDate;

            flatpickr(element, {
                dateFormat: "d/m/Y",
                locale: "pt",
                clear: true,
                mode: "range",
                minDate: getMinDate,
                maxDate: getMaxDate
            });
        });
    }

    const elementsRangeMonth = document.querySelectorAll('.flatpickr-range-month');
    if (elementsRangeMonth) {
        elementsRangeMonth.forEach(function (element) {
            var getMinDate = element.getAttribute("data-min-date");
            getMinDate = !getMinDate ? 'today' : getMinDate;

            var getMaxDate = element.getAttribute("data-max-date");
            getMaxDate = !getMaxDate ? 'today' : getMaxDate;

            flatpickr(element, {
                locale: 'pt',
                mode: "range",
                allowInput: false,
                static: true,
                altInput: true,
                minDate: getMinDate,
                maxDate: getMaxDate,
                plugins: [
                    new monthSelectPlugin({
                        shorthand: true,
                        dateFormat: "Y-m",
                        altFormat: "F/Y",
                        theme: "dark"
                    })
                ]
            });
        });
    }
}


export function maxLengthTextarea() {
    const textareas = document.querySelectorAll('.maxlength'); // Select all textareas with a maxlength attribute
    textareas.forEach(formComponent => {
        const maxLength = formComponent.getAttribute('maxlength');
        const counter = document.createElement('div');
        counter.className = 'counter badge bg-warning-subtle text-warning float-end';
        formComponent.parentNode.insertBefore(counter, formComponent.nextSibling);

        formComponent.addEventListener('input', function () {
            const currentLength = formComponent.value.length;
            counter.textContent = `${currentLength} / ${maxLength}`;
        });

        // Trigger the input event to set the initial counter value
        formComponent.dispatchEvent(new Event('input'));
    });
}

/**
 * Removes the 'was-validated' class from a form when any input changes.
 * @param {string} formSelector - The selector for the form.
 */
export function revalidationOnInput(formSelector = '.needs-validation') {
    if(formSelector.length){
        const form = document.querySelector(formSelector);

        if (!form) {
            console.warn('Form not found:', formSelector);

            return;
        }

        function removeValidationClass() {
            form.classList.remove('was-validated');
        }

        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(function(input) {
            input.addEventListener('input', removeValidationClass);
            input.addEventListener('select', removeValidationClass);
            input.addEventListener('textarea', removeValidationClass);
        });
    }
}


export function toggleTableRows() {
    // Get all the expand/collapse buttons
    var expandCollapseButtons = document.querySelectorAll('.btn-toggle-row-detail');

    if(expandCollapseButtons){
        // Function to close all detail rows
        function closeAllDetailRows() {
            document.querySelectorAll('.details-row').forEach(function(detailRow) {
                detailRow.classList.add('d-none'); // Hide all detail rows
            });
            document.querySelectorAll('.ri-folder-open-line').forEach(function(icon) {
                icon.classList.add('d-none'); // Hide all collapse icons
            });
            document.querySelectorAll('.ri-folder-line').forEach(function(icon) {
                icon.classList.remove('d-none'); // Show all expand icons
            });
        }

        // Add click event listener to each button
        expandCollapseButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var rowId = this.dataset.id;
                var detailsRow = document.querySelector('tr.details-row[data-details-for="' + rowId + '"]');
                var isCurrentlyOpen = !detailsRow.classList.contains('d-none');

                // Close all detail rows first
                closeAllDetailRows();

                // If the clicked row was not already open, toggle it
                if (!isCurrentlyOpen) {
                    detailsRow.classList.remove('d-none'); // Show the clicked detail row

                    // Toggle the icons for the clicked row
                    var iconExpand = this.querySelector('.ri-folder-line');
                    var iconCollapse = this.querySelector('.ri-folder-open-line');
                    iconExpand.classList.add('d-none');
                    iconCollapse.classList.remove('d-none');
                }

                this.blur();
            });
        });
    }
}

// Function to allow unchecking of radio buttons
export function allowUncheckRadioButtons(radioSelector = '.form-check-input') {
    document.addEventListener('click', function(event) {
        // Check if the clicked element is a radio button and if it's part of the selection we want to control
        if (event.target.matches(radioSelector)) {
            var radio = event.target;
            // If the radio button was already checked, uncheck it
            if (radio.dataset.checked) {
                radio.checked = false;
                radio.dataset.checked = ''; // Clear the custom data attribute
            } else {
                // Mark all radios with the same name as unchecked
                var allRadios = document.querySelectorAll('input[type="radio"][name="' + radio.name + '"]');
                allRadios.forEach(function(otherRadio) {
                    otherRadio.dataset.checked = '';
                });
                // Set the clicked one as checked
                radio.dataset.checked = 'true';
            }
        }
    }, true); // Use capturing to ensure we get the event first
}


export function layouRightSide(){
    var layoutRightSideBtn = document.querySelector('.layout-rightside-btn');
    if (layoutRightSideBtn) {
        Array.from(document.querySelectorAll(".layout-rightside-btn")).forEach(function (item) {
            var userProfileSidebar = document.querySelector(".layout-rightside-col");
            item.addEventListener("click", function () {
                if (userProfileSidebar.classList.contains("d-block")) {
                    userProfileSidebar.classList.remove("d-block");
                    userProfileSidebar.classList.add("d-none");
                } else {
                    userProfileSidebar.classList.remove("d-none");
                    userProfileSidebar.classList.add("d-block");
                }
            });
        });
        window.addEventListener("resize", function () {
            var userProfileSidebar = document.querySelector(".layout-rightside-col");
            if (userProfileSidebar) {
                Array.from(document.querySelectorAll(".layout-rightside-btn")).forEach(function () {
                    if (window.outerWidth < 1699 || window.outerWidth > 3440) {
                        userProfileSidebar.classList.remove("d-block");
                    } else if (window.outerWidth > 1699) {
                        userProfileSidebar.classList.add("d-block");
                    }
                });
            }

            var htmlAttr = document.documentElement;
            if (htmlAttr.getAttribute("data-layout") == "semibox") {
                userProfileSidebar.classList.remove("d-block");
                userProfileSidebar.classList.add("d-none");
            }
        });
        var overlay = document.querySelector('.overlay');
        if (overlay) {
            document.querySelector(".overlay").addEventListener("click", function () {
                if (document.querySelector(".layout-rightside-col").classList.contains('d-block') == true) {
                    document.querySelector(".layout-rightside-col").classList.remove("d-block");
                }
            });
        }
    }

    window.addEventListener("load", function () {
        var userProfileSidebar = document.querySelector(".layout-rightside-col");
        if (userProfileSidebar) {
            Array.from(document.querySelectorAll(".layout-rightside-btn")).forEach(function () {
                if (window.outerWidth < 1699 || window.outerWidth > 3440) {
                    userProfileSidebar.classList.remove("d-block");
                } else if (window.outerWidth > 1699) {
                    userProfileSidebar.classList.add("d-block");
                }
            });
        }

        var htmlAttr = document.documentElement

        if (htmlAttr.getAttribute("data-layout") == "semibox") {
            if (window.outerWidth > 1699) {
                userProfileSidebar.classList.remove("d-block");
                userProfileSidebar.classList.add("d-none");
            }
        }
    });
}

// Make the preview URL request
export function ajaxContentFromURL(idValue, url, targetId = 'load-preview', param = 'preview=true', elementId = 'content') {
    if (idValue) {
        var xhr = new XMLHttpRequest();

        //xhr.open('GET', url + '/' + idValue + '&preview=ture', true);
        xhr.open('GET', url + '/' + encodeURIComponent(idValue) + '?' + param, true);
        xhr.setRequestHeader('Cache-Control', 'no-cache, no-store, must-revalidate'); // Prevents caching
        xhr.setRequestHeader('Pragma', 'no-cache'); // For legacy HTTP 1.0 servers
        xhr.setRequestHeader('Expires', '0'); // Proxies
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    // Parse the response HTML
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(xhr.responseText, 'text/html');

                    // Extract the content of the div with the ID 'content'
                    if(elementId){
                        var contentDiv = doc.getElementById(elementId);
                        var contentHtml = contentDiv ? contentDiv.innerHTML : '';
                    }else{
                        var contentHtml = xhr.responseText;
                    }

                    // Update the preview div with the extracted content
                    if(contentHtml){
                        document.getElementById(targetId).innerHTML = contentHtml;

                        bsPopoverTooltip();
                    }
                } else {
                    // Handle error
                    toastAlert('Não foi possível carregar a pré-visualização', 'danger', 10000);
                }
            }
        };
        xhr.send();
    }
}

// Debounce function to limit the rate of invoking the save action
export function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this, args = arguments;

        clearTimeout(timeout);

        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}

export function toggleElement() {
    var toggle = document.querySelectorAll('.btn-toggle-element');

    if(toggle.length){
        toggle.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = this.getAttribute('data-toggle-target');

                var element = document.getElementById(target);
                if (element) {
                    if (element.style.display === "none") {
                        // If the div is hidden, show it
                        element.style.display = "block";

                        element.focus();
                    } else {
                        // If the div is shown, hide it
                        element.style.display = "none";
                    }
                }
            });
        });
    }
}

export function autoReloadPage(intervalInSeconds) {
    if(intervalInSeconds){
        setTimeout(function() {
            location.reload();
        }, intervalInSeconds * 1000);
    }
}

// Used on survey-surveyor.js and survey-auditor.js compliance radio labels
export function updateLabelClassesSurveyor(radios) {
    radios.forEach(radio => {
        const label = document.querySelector(`label[for="${radio.id}"]`);

        // Reset classes
        label.classList.remove('btn-success', 'btn-danger', 'btn-outline-success', 'btn-outline-danger');

        if (radio.checked) {
            if (radio.value === 'yes') {
                // Add and remove classes as needed when 'yes' radio is checked
                label.classList.add('btn-success');
                label.classList.remove('btn-outline-success');
            } else if (radio.value === 'no') {
                // Add and remove classes as needed when 'no' radio is checked
                label.classList.add('btn-danger');
                label.classList.remove('btn-outline-danger');
            }
        } else {
            // Add outline classes when radio is not checked
            if (radio.value === 'yes') {
                label.classList.add('btn-outline-success');
            } else if (radio.value === 'no') {
                label.classList.add('btn-outline-danger');
            }
        }
    });
}

export function updateLabelClassesAuditor(radios) {
    radios.forEach(radio => {
        const label = document.querySelector(`label[for="${radio.id}"]`);

        // Reset classes
        label.classList.remove('btn-secondary', 'btn-warning', 'btn-outline-secondary', 'btn-outline-warning');

        if (radio.checked) {
            if (radio.value === 'yes') {
                // Add and remove classes as needed when 'yes' radio is checked
                label.classList.add('btn-secondary');
                label.classList.remove('btn-outline-secondary');
            } else if (radio.value === 'no') {
                // Add and remove classes as needed when 'no' radio is checked
                label.classList.add('btn-warning');
                label.classList.remove('btn-outline-warning');
            }
        } else {
            // Add outline classes when radio is not checked
            if (radio.value === 'yes') {
                label.classList.add('btn-outline-secondary');
            } else if (radio.value === 'no') {
                label.classList.add('btn-outline-warning');
            }
        }
    });
}

// Uncheck each radio button
export function uncheckRadiosAndUpdateLabels(radios) {
    radios.forEach(radio => {
        // Uncheck the radio button
        radio.checked = false;

        const label = document.querySelector(`label[for="${radio.id}"]`);

        // Reset classes
        label.classList.remove('btn-success', 'btn-danger', 'btn-outline-success', 'btn-outline-danger');

        // Add outline classes when radio is not checked
        if (radio.value === 'yes') {
            label.classList.add('btn-outline-success');
        } else if (radio.value === 'no') {
            label.classList.add('btn-outline-danger');
        }
    });
}

// GLightbox Popup
// https://github.com/biati-digital/glightbox
export function lightbox(){
    if(document.querySelectorAll('.image-popup').length){
        var lightbox = GLightbox({
            selector: '.image-popup',
            title: false,
        });
    }
}


export const monthsInPortuguese = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
];

