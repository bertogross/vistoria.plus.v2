// resources/js/google-drive-api.js

const {google} = require('googleapis');
const OAuth2 = google.auth.OAuth2;

let oauth2Client;
let drive;

function initializeDriveAPI(clientId, clientSecret, redirectUrl, accessToken) {
    oauth2Client = new OAuth2(clientId, clientSecret, redirectUrl);

    // Set the access token if provided
    if (accessToken) {
        oauth2Client.setCredentials({
            access_token: accessToken
        });
    }

    drive = google.drive({
        version: 'v3',
        auth: oauth2Client
    });
}

function listFiles(callback) {
    if (!drive) {
        console.error('Drive API not initialized.');
        return;
    }

    drive.files.list({}, (err, res) => {
        if (err) {
            console.error('The API returned an error:', err);
            callback(err, null);
            return;
        }
        const files = res.data.files;
        callback(null, files);
    });
}

// TODO: Add more functions as needed for other Drive operations

module.exports = {
    initializeDriveAPI,
    listFiles
    // TODO: Export other functions as needed
};
