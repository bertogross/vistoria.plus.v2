import {
    toastAlert,
    formatPhoneNumber
} from './helpers.js';

import {
    attachImage
} from './settings-attachments.js';

document.addEventListener('DOMContentLoaded', function() {

    /*
    // Register the plugins
    FilePond.registerPlugin(
        FilePondPluginFileEncode,
        FilePondPluginFileValidateSize,
        FilePondPluginImageExifOrientation,
        FilePondPluginImagePreview
    );

    // Function to attach the FilePond instance to the input element
    function attachFilePondToLogo() {
        const inputElement = document.querySelector('.filepond-input-logo');

        if (inputElement) {
            const pond = FilePond.create(inputElement, {
                labelIdle: 'Arraste e Solte sua imagem ou <span class="filepond--label-action">buscar...</span>',
                imagePreviewHeight: 170,
                imageCropAspectRatio: '1:1',
                imageResizeTargetWidth: 200,
                imageResizeTargetHeight: 200,
                stylePanelLayout: 'compact circle',
                styleLoadIndicatorPosition: 'center bottom',
                styleProgressIndicatorPosition: 'right bottom',
                styleButtonRemoveItemPosition: 'left bottom',
                styleButtonProcessItemPosition: 'right bottom',
                allowImagePreview: true,
                allowRevert: true,
                server: {
                    url: uploadLogoURL,
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    onload: (response) => {
                        try {
                            const res = JSON.parse(response);
                            toastAlert(res.message, res.success ? 'success' : 'error');
                        } catch (e) {
                            toastAlert('Failed to parse server response', 'error');
                        }
                    },
                    onerror: (response) => {
                        try {
                            const res = JSON.parse(response);
                            toastAlert(res.message, 'error');
                        } catch (e) {
                            toastAlert('Failed to parse error response', 'error');
                        }
                    }
                }
            });

            // Set the initial file if it exists
                pond.addFile(inputElement.dataset.defaultFile);

                // Set up FilePond event listeners
                pond.on('processfile', (error, file) => {
                    if (error) {
                        toastAlert('Error during upload: ' + error, 'error');
                    } else {
                        toastAlert('File uploaded: ' + file.filename, 'success');
                    }
                });

                pond.on('error', (error) => {
                    toastAlert('FilePond error: ' + error.description, 'error');
                });


        } else {
            // Handle the case when the input element is not found
            toastAlert('Input element not found!', 'error');
        }
    }
    */

    if(uploadLogoURL){
        attachImage("#logo-image-input", "#logo-img", uploadLogoURL);
    }

    // Function to handle the deletion of a file
    const deleteLogoBtn = document.getElementById("btn-delete-logo");
    if (deleteLogoBtn) {
        deleteLogoBtn.addEventListener('click', async function(event) {
            event.preventDefault();

            fetch(deleteLogoURL, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {

                    console.log('File deleted successfully');

                    document.querySelector(`#logo-img`).setAttribute("src", assetURL + "build/images/no-logo.png");

                    document.querySelector(`#btn-delete-logo`).classList.add("d-none");

                    toastAlert(data.message, 'warning', 5000);
                } else {
                    console.error('Failed to delete file:', data.message);

                    toastAlert(data.message, 'danger', 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);

                toastAlert(error, 'danger', 5000);
            });
        });
    }




    // Call the functions when the DOM is fully loaded
    formatPhoneNumber();
    //attachFilePondToLogo();
});


