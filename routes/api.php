<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DropboxController;
use App\Http\Controllers\SurveysController;
use App\Http\Controllers\ClarifaiImageController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\StripeController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Stripe API
Route::prefix('stripe')->group(function () {
    Route::post('/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('stripeHandleWebhookURL');
});

// Dropbox API
Route::prefix('dropbox')->group(function () {
    //Route::get('/dropbox/redirect', [DropboxController::class, 'authorizeDropbox'])->name('DropboxRedirectURL');
    Route::get('/callback', [DropboxController::class, 'callback'])->name('DropboxCallbackURL');
    Route::get('/authorize', [DropboxController::class, 'authorizeDropbox'])->name('DropboxAuthorizeURL');
    Route::get('/deauthorize', [DropboxController::class, 'deauthorizeDropbox'])->name('DropboxDeauthorizeURL');
    Route::post('/upload', [DropboxController::class, 'uploadFile'])->name('DropboxUploadURL');
    Route::post('/delete', [DropboxController::class, 'deleteFile'])->name('DropboxDeleteURL');
    Route::get('/delete-folder/{path?}', [DropboxController::class, 'deleteFolder'])->name('DropboxDeleteFolderURL');
});

// Clarifai API
    // TODO

// SceneX API
    // TODO


