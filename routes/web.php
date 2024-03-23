<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use App\Models\UserConnections;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\{
    Auth\LoginController,
    Auth\RegisterController,
    HomeController,
    ProfileController,
    UserUploadController,
    SettingsConnectionsController,
    SettingsUserController,
    SettingsAccountController,
    TeamController,
    SurveysController,
    SurveysAuditController,
    SurveysTemplatesController,
    SurveysTermsController,
    SurveysResponsesController,
    SurveysAssignmentsController,
    SettingsApiKeysController,
    AttachmentsController,
    DropboxController,
    CompaniesController,
    SettingsStorageController,
    ClarifaiImageController,
    ScenexImageController,
    //PostmarkappController,
    StripeController
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();
//Language Translation
Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);

// Auth group
Route::middleware(['auth', 'set-dynamic-db-connection', 'check.authorization'])->group(function () {
    Route::get('/', [SurveysController::class, 'index'])->name('root');
    //Route::get('/', [HomeController::class, 'root'])->name('root');

    /*
    Route::get('/', function () {
        return view('index');
    })->middleware('role.redirect');
    */

    Route::post('/connections/change', [UserConnections::class, 'changeConnection'])->name('changeConnectionURL');

    // User Profile & Password Update
    Route::prefix('user')->group(function () {
        Route::post('/update-profile/{id}', [ProfileController::class, 'updateProfile'])->name('updateProfileURL');
        Route::post('/update-password/{id}', [ProfileController::class, 'updatePassword'])->name('updatePasswordURL');
    });

    Route::prefix('profile')->group(function () {
        Route::get('/{id?}', [ProfileController::class, 'index'])->name('profileShowURL');

        Route::post('/layout-mode', [ProfileController::class, 'changeLayoutMode'])->name('changeLayoutModeURL');
    });

    // Surveys Routes
    Route::prefix('surveys')->group(function () {
        Route::get('/', [SurveysController::class, 'index'])->name('surveysIndexURL');
        Route::get('/create', [SurveysController::class, 'create'])->name('surveysCreateURL');
            Route::get('/create/reload-users-tab/{id?}', [SurveysController::class, 'surveyReloadUsersTab'])->name('surveyReloadUsersTabURL');

        Route::get('/edit/{id?}', [SurveysController::class, 'edit'])->name('surveysEditURL')->where('id', '[0-9]+');
        Route::get('/show/{id?}', [SurveysController::class, 'show'])->name('surveysShowURL')->where('id', '[0-9]+');
        Route::post('/store/{id?}', [SurveysController::class, 'storeOrUpdate'])->name('surveysStoreOrUpdateURL');
        Route::post('/status', [SurveysController::class, 'changeStatus'])->name('surveysChangeStatusURL');

        Route::prefix('template')->group(function () {
            Route::get('/create', [SurveysTemplatesController::class, 'create'])->name('surveysTemplateCreateURL');
            Route::get('/edit/{id?}', [SurveysTemplatesController::class, 'edit'])->name('surveysTemplateEditURL')->where('id', '[0-9]+');
            Route::get('/preview/{id?}', [SurveysTemplatesController::class, 'previewFromSurveyTemplates'])->name('surveysTemplatePreviewFromSurveyTemplatesURL')->where('id', '[0-9]+');
            Route::get('/preview-from-warehouse/{id?}', [SurveysTemplatesController::class, 'previewFromWarehouse'])->name('surveysTemplatePreviewFromWarehouseURL')->where('id', '[0-9]+');
            Route::get('/selected-From-Warehouse/{id?}', [SurveysTemplatesController::class, 'selectedFromWarehouse'])->name('surveysTemplateSelectedFromWarehouseURL')->where('id', '[0-9]+');
            Route::get('/selected-From-SurveyTemplate/{id?}', [SurveysTemplatesController::class, 'selectedFromSurveyTemplates'])->name('surveysTemplateSelectedFromSurveyTemplateURL')->where('id', '[0-9]+');
            Route::post('/status', [SurveysTemplatesController::class, 'changeStatus'])->name('surveysTemplateChangeStatusURL');
            Route::post('/store/{id?}', [SurveysTemplatesController::class, 'storeOrUpdate'])->name('surveysTemplateStoreOrUpdateURL');
        });

        Route::prefix('assignment')->group(function () {
            Route::get('/show/{id?}', [SurveysAssignmentsController::class, 'show'])->name('assignmentShowURL')->where('id', '[0-9]+');

            Route::get('/surveyor-form/{id?}', [SurveysAssignmentsController::class, 'formSurveyorAssignment'])->name('formSurveyorAssignmentURL')->where('id', '[0-9]+');
            Route::post('/surveyor-status', [SurveysAssignmentsController::class, 'changeAssignmentSurveyorStatus'])->name('changeAssignmentSurveyorStatusURL');

            Route::get('/auditor-form/{id?}', [SurveysAssignmentsController::class, 'formAuditorAssignment'])->name('formAuditorAssignmentURL')->where('id', '[0-9]+');
            Route::post('/auditor-status', [SurveysAssignmentsController::class, 'changeAssignmentAuditorStatus'])->name('changeAssignmentAuditorStatusURL');
            Route::post('/auditor-enter', [SurveysAssignmentsController::class, 'enterAssignmentAuditor'])->name('enterAssignmentAuditorURL');
                Route::post('/auditor-revoke/{id?}', [SurveysAssignmentsController::class, 'revokeAssignmentAuditor'])->name('revokeAssignmentAuditorURL')->where('id', '[0-9]+');

            Route::get('/activities/{subDays?}', [SurveysAssignmentsController::class, 'requestAssignmentActivities'])->name('requestAssignmentActivitiesURL')->where('subDays', '[0-9]+');
        });

        Route::prefix('responses')->group(function () {
            Route::post('/surveyor/update/{id?}', [SurveysResponsesController::class, 'responsesSurveyorStoreOrUpdate'])->name('responsesSurveyorStoreOrUpdateURL');
            Route::post('/auditor/update/{id?}', [SurveysResponsesController::class, 'responsesAuditorStoreOrUpdate'])->name('responsesAuditorStoreOrUpdateURL');
        });

        Route::prefix('terms')->group(function () {
            // Terms Routes
            Route::get('/listing', [SurveysTermsController::class, 'index'])->name('surveysTermsIndexURL');
            Route::get('/create', [SurveysTermsController::class, 'create'])->name('surveysTermsCreateURL');
            Route::get('/form', [SurveysTermsController::class, 'form'])->name('surveysTermsFormURL');
            Route::get('/edit/{id?}', [SurveysTermsController::class, 'edit'])->name('surveysTermsEditURL');
            //Route::post('/store/{id?}', [SurveysTermsController::class, 'storeOrUpdate'])->name('surveysTermsStoreOrUpdateURL');
            Route::post('/store', [SurveysTermsController::class, 'storeOrUpdate'])->name('surveysTermsStoreOrUpdateURL');
            Route::get('/search', [SurveysTermsController::class, 'search'])->name('surveysTermsSearchURL');
        });

        // Audit Routes
        Route::get('/audits/{id?}', [SurveysAuditController::class, 'index'])->name('surveysAuditIndexURL');
    });

    // Admin Settings
    Route::middleware(['admin'])->group(function () {
        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingsAccountController::class, 'index'])->name('settingsIndexURL');
            Route::get('/account/{tab?}', [SettingsAccountController::class, 'show'])->name('settingsAccountShowURL');
                Route::post('/account/update', [SettingsAccountController::class, 'updateAccount'])->name('settingsAccountUpdateURL');
                Route::post('/account/user/update', [SettingsAccountController::class, 'updateUser'])->name('settingsAccountUserUpdateURL');

            Route::get('/api-keys', [SettingsApiKeysController::class, 'index'])->name('settingsApiKeysURL');

            //Route::get('/users', [SettingsUserController::class, 'index'])->name('settingsUsersIndexURL');
            Route::post('/users/store', [SettingsUserController::class, 'store'])->name('settingsUsersStoreURL');
                Route::post('/users/update/{id?}', [SettingsUserController::class, 'update'])->name('settingsUsersUpdateURL');
                //Route::get('/users/form/{id?}/{origin?}', [SettingsUserController::class, 'getUserFormContent'])->name('getUserFormContentURL');
                Route::post('/users/form', [SettingsUserController::class, 'getUserFormContent'])->name('getUserFormContentURL');

            Route::get('/companies', [CompaniesController::class, 'index'])->name('settingsCompaniesIndexURL');
            Route::post('/companies/update', [CompaniesController::class, 'storeOrUpdate'])->name('settingsCompaniesUpdateURL');

            Route::get('/connections', [SettingsConnectionsController::class, 'index'])->name('settingsConnectionsIndexURL');
            Route::post('/connection/revoke', [UserConnections::class, 'revokeConnection'])->name('revokeConnectionURL');
            Route::post('/connection/decision', [UserConnections::class, 'acceptOrDeclineConnection'])->name('acceptOrDeclineConnectionURL');

            Route::get('/storage', [SettingsStorageController::class, 'index'])->name('settingsStorageIndexURL');

            Route::get('/dropbox', [DropboxController::class, 'index'])->name('DropboxIndexURL');
            Route::get('/dropbox/browse/{path}', [DropboxController::class, 'browseFolder'])->name('DropboxBrowseFolderURL');

            Route::post('/clarifai', [ClarifaiImageController::class, 'submit'])->name('ClarifaiSubmitURL');
            Route::post('/scenex', [ScenexImageController::class, 'submit'])->name('ScenexSubmitURL');

            Route::post('/stripe/subscription', [StripeController::class, 'createStripeSession'])->name('stripeSubscriptionURL');
            Route::post('/stripe/cancel-subscription', [StripeController::class, 'cancelStripeSubscriptions'])->name('stripeCancelSubscriptionURL');
            //Route::post('/stripe/subscription/details', [StripeController::class, 'updateStripeSubscriptionItem'])->name('stripeSubscriptionDetailsURL');
            // Route::post('/stripe/cart/addon', [StripeController::class, 'addonCart'])->name('stripeCartAddonURL');

        });
    });

    // Team Routes
    Route::get('/team', [TeamController::class, 'index'])->name('teamIndexURL');

    // File Upload Routes
    Route::prefix('upload')->group(function () {
        Route::post('/avatar', [UserUploadController::class, 'uploadAvatar'])->name('uploadAvatarURL');
        Route::post('/cover', [UserUploadController::class, 'uploadCover'])->name('uploadCoverURL');
        Route::post('/logo', [UserUploadController::class, 'uploadLogo'])->name('uploadLogoURL');
        Route::delete('/delete/logo', [UserUploadController::class, 'deleteLogo'])->name('deleteLogoURL');

        Route::post('/photo', [AttachmentsController::class, 'uploadPhoto'])->name('uploadPhotoURL');
        Route::delete('/delete/photo/{id?}', [AttachmentsController::class, 'deletePhoto'])->name('deletePhotoURL');
        Route::delete('/delete/attachment', [AttachmentsController::class, 'deleteAttachmentByPath'])->name('deleteAttachmentByPathURL');
    });


});

Route::get('/unauthorized', function () {
    return view('errors.unauthorized');
})->name('unauthorized');
Route::get('/check-authorization', [UserConnections::class, 'preventUnauthorizedConnection'])->name('checkAuthorizationURL');

Route::post('/login', [LoginController::class, 'login'])->name('loginURL');

Route::post('/register', [RegisterController::class, 'register'])->name('registerURL');
    Route::get('/register-success', [RegisterController::class, 'welcome'])->name('registerSuccessURL');
    Route::get('/invitation/{code?}', [RegisterController::class, 'invitationResponse'])->name('invitationResponseURL');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Reset password routes
Route::prefix('forgot-password')->group(function () {
    Route::get('/email', function () {
        return view('auth.passwords.email');
    })->middleware('guest')->name('passwordRequestFormURL');

    Route::post('/send', [ResetPasswordController::class, 'sendResetLink'])->name('passwordSendResetLinkURL');

    Route::get('/token/{token}/{email}', [ResetPasswordController::class, 'showResetForm'])->name('passwordResetFormURL');

    Route::post('/reset', [ResetPasswordController::class, 'resetPassword'])->name('passwordResetURL');
});

/*
//use Illuminate\Support\Facades\DB;
Route::get('/test-db', function () {
    try {
        DB::connection('vpAppTemplate')->getPdo();
        return 'Connection to vpAppTemplate is OK!';
    } catch (\Exception $e) {
        return 'Error connecting to vpAppTemplate: ' . $e->getMessage();
    }
});

//To make testing on offline view layout
Route::get('/offline', function () {
    return view('vendor.laravelpwa.offline');
});
*/

//Route::get('/send-email', [PostmarkappController::class, 'sendEmail']);

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

// Catch-All Route
Route::get('{any}', [HomeController::class, 'index'])->where('any', '.*')->name('index');

