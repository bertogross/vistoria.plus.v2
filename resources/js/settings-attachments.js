import {
    toastAlert
} from './helpers.js';

// Attach event listeners for Avatar and Cover image upload
export function attachImage(inputSelector, imageSelector, uploadUrl, withPreviewCard = false) {
    const inputFile = document.querySelector(inputSelector);

    if (inputFile) {
        inputFile.addEventListener("change", function() {
            const preview = document.querySelector(imageSelector);

            const userID = preview.getAttribute("data-user-id") ?? false;

            //console.log("userID:", userID);
            //console.log("Selector:", `${imageSelector}-${userID}`);

            var previewCard = false;
            if( withPreviewCard && userID){
                previewCard = document.querySelector(imageSelector+'-'+userID);
            }
            const file = inputFile.files[0];
            const reader = new FileReader();

            reader.addEventListener("load", function() {
                //preview.src = reader.result;
                //console.log("Image source:", preview.src);

                const img = new Image();
                img.src = reader.result;
                //console.log("Image source:", img.src);

                img.onload = function() {
                    //console.log("Image loaded with dimensions:", img.width, "x", img.height);

                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    if (imageSelector == '#avatar-img') {
                        canvas.width = 200;
                        canvas.height = 200;
                        //console.log("Canvas dimensions:", canvas.width, "x", canvas.height);

                        const aspectRatio = img.width / img.height;
                        let sourceX, sourceY, sourceWidth, sourceHeight;

                        if (aspectRatio > 1) {
                            sourceWidth = img.height;
                            sourceHeight = img.height;
                            sourceX = (img.width - sourceWidth) / 2;
                            sourceY = 0;
                        } else if (aspectRatio < 1) {
                            sourceWidth = img.width;
                            sourceHeight = img.width;
                            sourceX = 0;
                            sourceY = (img.height - sourceHeight) / 2;
                        } else {
                            sourceWidth = img.width;
                            sourceHeight = img.height;
                            sourceX = 0;
                            sourceY = 0;
                        }
                        //console.log("Source dimensions and positions:", sourceX, sourceY, sourceWidth, sourceHeight);

                        ctx.drawImage(img, sourceX, sourceY, sourceWidth, sourceHeight, 0, 0, canvas.width, canvas.height);
                    }else if (imageSelector == '#logo-img') {

                        // Set maximum dimensions for logo
                        const maxLogoWidth = 200;
                        const maxLogoHeight = 200;
                        //console.log("Canvas dimensions:", canvas.width, "x", canvas.height);

                        // Calculate aspect ratio for scaling
                        const aspectRatio = img.width / img.height;

                        // Determine the target dimensions while maintaining the aspect ratio
                        let targetWidth = aspectRatio >= maxLogoWidth / maxLogoHeight ? maxLogoWidth : Math.min(img.width, maxLogoWidth);
                        let targetHeight = aspectRatio < maxLogoWidth / maxLogoHeight ? maxLogoHeight : Math.min(img.height, maxLogoHeight);

                        // Adjust target dimensions if the image is smaller than the max dimensions
                        if (img.width < maxLogoWidth && img.height < maxLogoHeight) {
                            targetWidth = img.width;
                            targetHeight = img.height;
                        }

                        // Set canvas dimensions
                        canvas.width = targetWidth;
                        canvas.height = targetHeight;

                        // Calculate the source dimensions
                        let sourceWidth = img.width;
                        let sourceHeight = img.height;
                        let sourceX = 0;
                        let sourceY = 0;

                        // Draw the image on the canvas
                        ctx.drawImage(img, sourceX, sourceY, sourceWidth, sourceHeight, 0, 0, targetWidth, targetHeight);
                    } else {
                        let targetWidth = img.width;
                        let targetHeight = img.height;

                        if (targetWidth > 1919 || targetHeight > 1919) {
                            const aspectRatio = targetWidth / targetHeight;
                            if (targetWidth > targetHeight) {
                                targetWidth = 1731;
                                targetHeight = targetWidth / aspectRatio;
                            } else {
                                targetHeight = 1731;
                                targetWidth = targetHeight * aspectRatio;
                            }
                        }

                        canvas.width = targetWidth;
                        canvas.height = targetHeight;
                        ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
                    }

                    canvas.toBlob(function(blob) {
                        const formData = new FormData();
                        formData.append('file', blob, file.name);
                        if(userID){
                            formData.append('user_id', userID);
                        }

                        //console.log("Blob size:", blob.size);

                        fetch(uploadUrl, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                toastAlert(data.message, 'success');
                                if (data.path) {
                                    if(preview){
                                        preview.src = '/storage/' + data.path;
                                    }
                                    if(previewCard){
                                        previewCard.src = '/storage/' + data.path;
                                    }
                                }

                                if (imageSelector == '#logo-img'){
                                    document.querySelector(`#btn-delete-logo`).classList.remove("d-none");
                                }
                            } else {
                                toastAlert(data.message, 'danger');
                            }
                        })
                        .catch(error => {
                            toastAlert('Upload failed: ' + error, 'danger');
                            console.error('Error:', error);
                        });
                    }, file.type === 'image/png' ? 'image/png' : 'image/jpeg', file.type === 'image/png' ? 1 : 0.6);
                };
            }, false);

            if (file) {
                reader.readAsDataURL(file);
            }
        });
    }
}
