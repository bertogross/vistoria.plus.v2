<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DropboxController;
use App\Http\Controllers\SurveysController;
use App\Http\Controllers\ClarifaiImageController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SettingsStripeController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Stripe API
Route::post('/stripe/subscription', [SettingsStripeController::class, 'createStripeSession'])->name('stripeSubscriptionURL');
Route::post('/stripe/subscription/details', [SettingsStripeController::class, 'updateSubscriptionItem'])->name('stripeSubscriptionDetailsURL');
Route::post('/stripe/cart/addon', [SettingsStripeController::class, 'addonCart'])->name('stripeCartAddonURL');
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);


// Dropbox API
//Route::get('/dropbox/redirect', [DropboxController::class, 'authorizeDropbox'])->name('DropboxRedirectURL');
Route::get('/dropbox/callback', [DropboxController::class, 'callback'])->name('DropboxCallbackURL');
Route::get('/dropbox/authorize', [DropboxController::class, 'authorizeDropbox'])->name('DropboxAuthorizeURL');
Route::get('/dropbox/deauthorize', [DropboxController::class, 'deauthorizeDropbox'])->name('DropboxDeauthorizeURL');
Route::post('/dropbox/upload', [DropboxController::class, 'uploadFile'])->name('DropboxUploadURL');
Route::post('/dropbox/delete', [DropboxController::class, 'deleteFile'])->name('DropboxDeleteURL');
Route::get('/dropbox/delete-folder/{path?}', [DropboxController::class, 'deleteFolder'])->name('DropboxDeleteFolderURL');

// Clarifai API
    // TODO

// SceneX API
    // TODO


Route::get('/invitation/{code?}', [InvitationController::class, 'invitationResponse'])->name('invitationResponseURL');
