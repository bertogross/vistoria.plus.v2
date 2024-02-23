<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Image Submission</title>
    <style>
        #results{
            width: 700px;
            height: 700px;
        }
    </style>
</head>
<body>
<form enctype="multipart/form-data">
    @csrf
    <input type="file" id="imageInput">
    <br>
    <textarea id="textInput"></textarea>
    <br>
    <button id="submitButton">Submit</button>
</form>

<div id="results"></div>

<script>
const PAT = 'a6871127de264bc284aa06b412132753';
const USER_ID = 'clarifai';
const APP_ID = 'main';
const MODEL_ID = 'moderation-recognition';
const MODEL_VERSION_ID = 'aa8be956dbaa4b7a858826a84253cab9';

document.getElementById('imageInput').addEventListener('change', function (event) {
    const file = event.target.files[0];

    if (!file) {
        alert('Please select an image.');
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        const base64Image = e.target.result.split(',')[1];

        const raw = JSON.stringify({
            "user_app_id": {
                "user_id": USER_ID,
                "app_id": APP_ID
            },
            "inputs": [
                {
                    "data": {
                        "image": {
                            "base64": base64Image
                        }
                    }
                }
            ]
        });

        const requestOptions = {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Key ' + PAT
            },
            body: raw
        };

        const proxyUrl = ''; // https://cors-anywhere.herokuapp.com/
        const targetUrl = 'https://api.clarifai.com/v2/models/' + MODEL_ID + '/versions/' + MODEL_VERSION_ID + '/outputs';

        fetch(proxyUrl + targetUrl, requestOptions)
            .then(response => response.text())
            .then(result => {
                console.log(result);
                document.getElementById('results').innerHTML = result;
            })
            .catch(error => console.log('error', error));
    };

    reader.readAsDataURL(file);
});
</script>
</body>
</html>
