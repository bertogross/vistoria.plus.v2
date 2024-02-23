import {
    toastAlert,
    lightbox,
    showPreloader
} from './helpers.js';

document.addEventListener('DOMContentLoaded', function() {

    // Attach event listeners for Photos image upload
    function insertUploadProgress() {
        const uploadProgressHTML = `
            <div id="uploadProgress" class="fixed-bottom mb-5 ms-auto me-auto">
                <div class="flex-grow-1">
                    <div class="progress animated-progress progress-label bg-white">
                        <div id="progressBar" class="progress-bar bg-theme" role="progressbar" style="width: 1%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <div id="progressText" class="label">Inicializando...</div>
                        </div>
                    </div>
                </div>
            </div>`;
        document.body.insertAdjacentHTML('beforeend', uploadProgressHTML);
    }
    function removeUploadProgress(){
        var element = document.getElementById('uploadProgress');
        if(element){
            element.remove();
        }
    }
    function attachPhoto(inputFile, uploadUrl, onSuccess) {
        if (inputFile) {
            const file = inputFile.files[0];

            if (!file.type.startsWith('image/')) {
                console.error('File is not an image.');
                toastAlert('O arquivo deve ser uma imagem', 'warning', 15000);
                return;
            }

            // Check if the file is a JPEG or JPG image
            if (!file.type.match('image/jpeg')) {
                console.error('File is not a JPEG or JPG image.');
                toastAlert('O arquivo deve ser JPG', 'warning', 15000);
                return;
            }

            showPreloader();

            insertUploadProgress();

            const reader = new FileReader();

            reader.onload = function(event) {
                const img = new Image();
                img.src = event.target.result;

                img.onload = function() {
                    if (!img.complete || img.naturalWidth === 0) {
                        console.error('Failed to load image.');
                        toastAlert('Falha ao carregar o arquivo', 'danger', 15000);
                        return;
                    }

                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    let targetWidth = img.width;
                    let targetHeight = img.height;

                    // Resize logic
                    if (targetWidth > 1920 || targetHeight > 1920) {
                        const aspectRatio = targetWidth / targetHeight;
                        if (targetWidth > targetHeight) {
                            targetWidth = 1920;
                            targetHeight = targetWidth / aspectRatio;
                        } else {
                            targetHeight = 1920;
                            targetWidth = targetHeight * aspectRatio;
                        }
                    }

                    canvas.width = targetWidth;
                    canvas.height = targetHeight;
                    ctx.drawImage(img, 0, 0, targetWidth, targetHeight);

                    canvas.toBlob(function(blob) {
                        if (!blob) {
                            console.error('Failed to create blob.');
                            toastAlert('Falha ao redimensionar o arquivo', 'danger', 15000);
                            return;
                        }

                        const formData = new FormData();
                        formData.append('file', blob, file.name);

                        // Use XMLHttpRequest for upload
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', uploadUrl, true);

                        // Set up the request events
                        xhr.upload.onprogress = function(event) {
                            if (event.lengthComputable) {
                                const percentComplete = Math.round((event.loaded / event.total) * 100);
                                const progressBar = document.getElementById('progressBar');
                                const progressText = document.getElementById('progressText');

                                progressBar.style.width = percentComplete + '%';
                                progressBar.setAttribute('aria-valuenow', percentComplete);
                                progressText.innerText = percentComplete > 98 ? 'Armazenando...' : percentComplete + '%';
                            }
                        };

                        xhr.onload = function() {
                            if (xhr.status === 200) {
                                const data = JSON.parse(xhr.responseText);
                                if (data.success) {
                                    toastAlert(data.message, 'success', 5000);
                                    onSuccess(inputFile, data);

                                    // Trigger a click event on the closest update button
                                    const container = document.querySelector(`#element-attachment-${data.id}`).closest('.responses-data-container');
                                    if (container) {
                                        const updateButton = container.querySelector('.btn-response-update');
                                        if (updateButton) {
                                            setTimeout(() => {
                                                updateButton.click();
                                            }, 2000);
                                        }
                                    }

                                    removeUploadProgress();
                                } else {
                                    toastAlert(data.message, 'danger', 15000);

                                    removeUploadProgress();
                                }
                            } else {
                                toastAlert('Upload failed: ' + xhr.statusText, 'danger');
                            }

                            removeUploadProgress();

                            showPreloader(false);
                        };

                        xhr.onerror = function() {
                            console.error('Upload failed: ' + xhr.statusText);
                            toastAlert('O envio do arquivo falhou', 'danger', 15000);

                            removeUploadProgress();

                            showPreloader(false);;
                        };

                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));


                        // Send the request
                        xhr.send(formData);
                    }, file.type === 'image/png' ? 'image/png' : 'image/jpeg', file.type === 'image/png' ? 1 : 0.6);
                };

                img.onerror = function() {
                    console.error('Error in loading image.');
                    toastAlert('Erro ao carregar arquivo', 'danger', 15000);

                    removeUploadProgress();

                    showPreloader(false);
                };
            };

            reader.onerror = function() {
                console.error('Error reading file.');
                toastAlert('Erro na leitura do arquivo', 'danger', 15000);

                removeUploadProgress();

                showPreloader(false);
            };

            reader.readAsDataURL(file);
        }
    }

    // Function to handle the deletion of a file
    function deletePhoto(fileId) {
        fetch(deletePhotoURL + '/' + fileId, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {

                // Trigger a click event on the closest update button
                const container = document.querySelector(`#element-attachment-${fileId}`).closest('.responses-data-container');
                if (container) {
                    const updateButton = container.querySelector('.btn-response-update');
                    if (updateButton) {
                        setTimeout(() => {
                            updateButton.click();
                        }, 3000);
                    }
                }

                console.log('File deleted successfully');
                // Remove the element from the UI
                document.querySelector(`#element-attachment-${fileId}`).remove();

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
    }

    // Attach event listeners to all delete buttons
    function deletePhotoButtonsListener() {
        const deletePhotoButtons = document.querySelectorAll('.btn-delete-photo');
        if (deletePhotoButtons) {
            deletePhotoButtons.forEach(button => {
                button.removeEventListener('click', onDeleteClick); // Remove any existing listeners to avoid duplicates
                button.addEventListener('click', onDeleteClick);
            });
        }
    }

    function onDeleteClick(event) {
        event.preventDefault();
        const fileId = this.getAttribute('data-attachment-id');
        if (confirm('Tem certeza de que deseja excluir este arquivo?')) {
            deletePhoto(fileId);

            const responsesData = this.closest('.responses-data-container');

            const attachmentInputs = responsesData.querySelectorAll('input[name="attachment_id[]"]');
            const attachmentIds = Array.from(attachmentInputs).map(input => input.value);
            if (attachmentIds.length === 0) {

                var pendingIcon = responsesData.querySelector('.ri-time-line');
                var completedIcon = responsesData.querySelector('.ri-check-double-fill');

                // If responseId is not set, show the pending icon and hide the completed icon
                if (pendingIcon) pendingIcon.classList.remove('d-none');
                if (completedIcon) completedIcon.classList.add('d-none');

                document.querySelector('#btn-response-finalize').classList.add('d-none');

            }
        }
    }

    // Call this function initially to attach listeners to existing delete buttons
    deletePhotoButtonsListener();

    // Upload logic
    const uploadSurveyPhotoInputs = document.querySelectorAll('.input-upload-photo');
    if (uploadSurveyPhotoInputs) {
        uploadSurveyPhotoInputs.forEach(function(input) {
            input.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const uploadUrl = uploadPhotoURL;
                    attachPhoto(this, uploadUrl, function(inputFile, data) {
                        const parentForm = inputFile.closest('.responses-data-container');

                        // Handle successful upload
                        const galleryWrapper = parentForm.querySelector('.gallery-wrapper');
                        if (galleryWrapper) {
                            const galleryItemHtml = `
                                <div id="element-attachment-${data.id}" class="element-item col-auto">
                                    <div class="gallery-box card p-0 mb-0 mt-1">
                                        <div class="gallery-container">
                                            <a href="/storage/${data.path}" class="image-popup">
                                                <img class="rounded gallery-img" alt="image" height="70" src="${assetURL}storage/${data.path}">
                                            </a>
                                        </div>
                                    </div>
                                    <div class="position-absolute translate-middle mt-n2 ms-2">
                                        <div class="avatar-xs">
                                            <button type="button" class="avatar-title bg-light border-0 rounded-circle text-danger cursor-pointer btn-delete-photo" data-attachment-id="${data.id}" title="Deletar Arquivo">
                                                <i class="ri-delete-bin-2-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="attachment_id[]" value="${data.id}">
                                </div>`;
                            galleryWrapper.insertAdjacentHTML('beforeend', galleryItemHtml);

                            lightbox();
                        }

                        // Call the listener initialization function after appending new HTML
                        deletePhotoButtonsListener();
                    });
                }
            });
        });
    }


});

