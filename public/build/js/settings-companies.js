import {
    toastAlert,
} from './helpers.js';

document.addEventListener('DOMContentLoaded', function() {
    const addCompanyRowButton = document.getElementById('btn-add-company-row');
    const table = document.getElementById('companiesTable');
    const tbody = table.getElementsByTagName('tbody')[0];

    if(addCompanyRowButton){
        addCompanyRowButton.addEventListener('click', function(event) {
            const newIndex = tbody.rows.length; // Assuming no other rows are being deleted

            // Create new row and cells
            const newRow = tbody.insertRow();
            const statusCell = newRow.insertCell(0);
            const idCell = newRow.insertCell(1);
            const nameCell = newRow.insertCell(2);

            // Add content to cells
            statusCell.innerHTML = `
                <div class="form-check form-switch form-switch-md form-switch-theme ms-3" title="Ativar/Desativar">
                    <input type="checkbox" checked class="form-check-input" name="companies[${newIndex}][status]">
                </div>
            `;

            idCell.innerHTML = `
                <input type="hidden" name="companies[${newIndex}][id]" value="New">
                <div class="text-warning small">Nova</div>
            `; // Adjust based on how you want to handle new company IDs

            nameCell.innerHTML = `
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" name="companies[${newIndex}][name]" value="" maxlength="50" placeholder="Digite o nome da unidade aqui" required>
                    <button class="btn btn-outline-light text-danger btn-delete-row" type="button"><i class="ri-delete-bin-2-line"></i></button>
                </div>
            `;

            // Optionally, focus the name input of the new row for convenience
            nameCell.querySelector('input').focus();

            attachDeleteEventListeners();

        });

        // Event delegation for deleting a row
        function attachDeleteEventListeners() {
            // Select all delete buttons
            const deleteButtons = document.querySelectorAll('.btn-delete-row');

            if(deleteButtons){
                // Attach click event listeners to each delete button
                deleteButtons.forEach(button => {
                    button.removeEventListener('click', removeRow); // Remove existing event listener to prevent duplicates
                    button.addEventListener('click', removeRow); // Add new event listener
                });
            }
        }

        function removeRow(event) {
            // Confirm before deleting the row
            const isConfirmed = confirm('Remover unidade?');
            if (isConfirmed) {
                const row = event.target.closest('tr');
                if (row) {
                    row.remove();
                    toastAlert('Unidade removida', 'success');
                }
            }
        }

        // Remember to call attachDeleteEventListeners() again after dynamically adding a new row

        /*tbody.addEventListener('click', function(event) {
            if (event.target && event.target.classList.contains('btn-delete-row')) {
                // Confirm before deleting the row
                const isConfirmed = confirm('Remover unidade?');
                if (isConfirmed) {
                    const row = event.target.closest('tr');
                    if (row) {
                        row.remove();
                        toastAlert('Unidade removida', 'success');
                    }
                }
            }
        });*/


    }

});

