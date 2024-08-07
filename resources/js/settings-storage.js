import {toastAlert, formatSize} from './helpers.js';

document.addEventListener('DOMContentLoaded', function() {
    // Set the file type filter based on the URL parameter
    const url = new URL(window.location.href);
    const fileType = url.searchParams.get('fileType');
    const fileTypeSelect = document.getElementById('file-type');
    if (fileType) {
        fileTypeSelect.value = fileType;
    } else {
        fileTypeSelect.value = 'All'; // default value
    }

    // Filter files by file type
    function filterByFileType() {
        const fileType = document.getElementById('file-type').value;
        const url = new URL(window.location.href);
        url.searchParams.set('fileType', fileType);
        window.location.href = url.toString();
    }

    // Handle file uploads
    document.getElementById('inputGroupFile').addEventListener('change', function() {
        const fileInput = document.getElementById('inputGroupFile');
        const files = fileInput.files;
        //console.log('Files:', files);

        if (files.length > 0) {

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                //console.log('File:', file);

                const reader = new FileReader();
                reader.onload = function(event) {
                    const data = event.target.result;

                    fetch(DropboxUploadURL, {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + DropboxAccessToken,
                            'Content-Type': 'application/octet-stream',
                            'Dropbox-API-Arg': JSON.stringify({
                                path: DropboxCurrentFolderPath + '/' + file.name,
                                mode: 'add',
                                autorename: true,
                                mute: false,
                            }),
                        },
                        body: data,
                    })
                    .then(response => response.json())
                    .then(data => {
                        //console.log(data);

                        toastAlert('Uploading file success', 'success', 10000);
                        addFileRow(data);

                        // Clear the file input
                        fileInput.value = '';
                    })
                    .catch(error => {
                        console.error('Error uploading file:', error);
                        toastAlert('Error uploading file', 'danger', 10000);
                    });
                };

                reader.readAsArrayBuffer(file);
            }
        }
    });

    // Handle file deletions
    function deleteFile(){
        const deleteButtons = document.querySelectorAll('.btn-delete-file');
        deleteButtons.forEach(button => {
            if(deleteButtons){
                button.addEventListener('click', function (event) {
                    event.preventDefault();

                    this.blur();

                    if (confirm('Tem certeza de que deseja excluir este arquivo?')) {
                        const path = this.dataset.path;
                        const fileId = this.dataset.id;
                        fetch(DropboxDeleteURL + '?path=' + encodeURIComponent(path), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': 'Bearer ' + DropboxAccessToken,
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            //console.log(data);

                            if (data.success) {
                                deleteFileRow(fileId);

                                toastAlert('Success to delete file', 'success', 10000);
                            } else {
                                toastAlert('Failed to delete file', 'danger', 10000);
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting file:', error);
                            toastAlert('Error deleting file', 'danger', 10000);
                        });
                    }
                });
            }
        });
    }

    // Remove a file row from the file list
    function deleteFileRow(fileId) {
        const rows = document.querySelectorAll('#file-list tr[data-file-id="' + fileId + '"]');
        rows.forEach(row => {
            row.remove();
        });
    }

    // Add a file row to the file list
    function addFileRow(file) {
        //console.log('File:', file);

        const tbody = document.getElementById('file-list');
        const tr = document.createElement('tr');
        tr.setAttribute('data-file-id', file.id);

        const fileType = file.name.split('.').pop().toLowerCase();
        let fileIcon = '<i class="ri-file-fill align-bottom text-primary"></i>';
        if (['jpg', 'jpeg', 'png', 'gif'].includes(fileType)) {
            fileIcon = '<i class="ri-gallery-fill align-bottom text-success"></i>';
        } else if (fileType === 'pdf') {
            fileIcon = '<i class="ri-file-pdf-fill align-bottom text-danger"></i>';
        } else if (['txt', 'doc', 'docx'].includes(fileType)) {
            fileIcon = '<i class="ri-file-text-fill align-bottom text-secondary"></i>';
        }

        const serverModified = new Date(file.server_modified);
        const formattedDate = serverModified.toLocaleDateString();

        tr.innerHTML = `
            <td>${fileIcon} ${file.name}</td>
            <td>${formatSize(file.size)}</td>
            <td>${formattedDate}</td>
            <td class="text-end">
                <div class="btn-group">
                    <a href="${file.link}" download class="btn btn-sm btn-outline-dark d-none" title="Download"><i class="ri-download-2-line"></i></a>

                    <button class="btn btn-sm btn-outline-dark btn-delete-file" data-id="${file.id}" data-path="${file.path_display}" title="Deletar">
                        <i class="ri-delete-bin-5-line text-danger"></i>
                    </button>
                </div>
            </td>
        `;

        tbody.insertBefore(tr, tbody.firstChild);

        deleteFile();
    }


    // Call the function when the DOM is fully loaded
    deleteFile();

});

