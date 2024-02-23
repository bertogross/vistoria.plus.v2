<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\{
    Auth\LoginController,
    HomeController,
    ProfileController,
    UserUploadController,
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
    StorageController,
    ClarifaiImageController,
    ScenexImageController
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

/*
Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->name('root');

//Update User Details
Route::post('/update-profile/{id}', [App\Http\Controllers\HomeController::class, 'updateProfile'])->name('updateProfile');
Route::post('/update-password/{id}', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('updatePassword');

Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
*/

// Auth group
Route::middleware(['auth', 'set-db-connection'])->group(function () {
    //Route::get('/', [SurveysController::class, 'index'])->name('root');
    Route::get('/', [HomeController::class, 'root'])->name('root');

    /*
    Route::get('/', function () {
        return view('index');
    })->middleware('role.redirect');
    */

    // User Profile & Password Update
    Route::prefix('user')->group(function () {
        Route::post('/update-profile/{id}', [ProfileController::class, 'updateProfile'])->name('updateProfileURL');
        Route::post('/update-password/{id}', [ProfileController::class, 'updatePassword'])->name('updatePasswordURL');
    });
    Route::get('/profile/{id?}', [ProfileController::class, 'index'])->name('profileShowURL');

    Route::post('/profile/layout-mode', [ProfileController::class, 'changeLayoutMode'])->name('profileChangeLayoutModeURL');

    Route::post('/profile/connection', [ProfileController::class, 'changeConnection'])->name('profileChangeConnectionURL');

    // Surveys Routes
    Route::prefix('surveys')->group(function () {
        Route::get('/', [SurveysController::class, 'index'])->name('surveysIndexURL');
        Route::get('/create', [SurveysController::class, 'create'])->name('surveysCreateURL');
        Route::get('/edit/{id?}', [SurveysController::class, 'edit'])->name('surveysEditURL')->where('id', '[0-9]+');
        Route::get('/show/{id?}', [SurveysController::class, 'show'])->name('surveysShowURL')->where('id', '[0-9]+');
        Route::post('/store/{id?}', [SurveysController::class, 'storeOrUpdate'])->name('surveysStoreOrUpdateURL');
        Route::post('/status', [SurveysController::class, 'changeStatus'])->name('surveysChangeStatusURL');

            //Route::get('/listing', [SurveysTemplatesController::class, 'index'])->name('surveyTemplateIndexURL');
            Route::get('/template/create', [SurveysTemplatesController::class, 'create'])->name('surveysTemplateCreateURL');
            Route::get('/template/edit/{id?}', [SurveysTemplatesController::class, 'edit'])->name('surveysTemplateEditURL')->where('id', '[0-9]+');
            Route::get('/template/preview/{id?}', [SurveysTemplatesController::class, 'previewFromSurveyTemplates'])->name('surveysTemplatePreviewFromSurveyTemplatesURL')->where('id', '[0-9]+');
            Route::get('/template/preview-from-warehouse/{id?}', [SurveysTemplatesController::class, 'previewFromWarehouse'])->name('surveysTemplatePreviewFromWarehouseURL')->where('id', '[0-9]+');
            Route::get('/template/selected-From-Warehouse/{id?}', [SurveysTemplatesController::class, 'selectedFromWarehouse'])->name('surveysTemplateSelectedFromWarehouseURL')->where('id', '[0-9]+');
            Route::get('/template/selected-From-SurveyTemplate/{id?}', [SurveysTemplatesController::class, 'selectedFromSurveyTemplates'])->name('surveysTemplateSelectedFromSurveyTemplateURL')->where('id', '[0-9]+');
            Route::post('/template/status', [SurveysTemplatesController::class, 'changeStatus'])->name('surveysTemplateChangeStatusURL');

            Route::post('/template/store/{id?}', [SurveysTemplatesController::class, 'storeOrUpdate'])->name('surveysTemplateStoreOrUpdateURL');

            Route::get('/assignment/show/{id?}', [SurveysAssignmentsController::class, 'show'])->name('assignmentShowURL')->where('id', '[0-9]+');

            Route::get('/assignment/surveyor-form/{id?}', [SurveysAssignmentsController::class, 'formSurveyorAssignment'])->name('formSurveyorAssignmentURL')->where('id', '[0-9]+');
            Route::post('/assignment/surveyor-status', [SurveysAssignmentsController::class, 'changeAssignmentSurveyorStatus'])->name('changeAssignmentSurveyorStatusURL');

            Route::get('/assignment/auditor-form/{id?}', [SurveysAssignmentsController::class, 'formAuditorAssignment'])->name('formAuditorAssignmentURL')->where('id', '[0-9]+');
            Route::post('/assignment/auditor-status', [SurveysAssignmentsController::class, 'changeAssignmentAuditorStatus'])->name('changeAssignmentAuditorStatusURL');
            Route::post('/assignment/auditor-enter', [SurveysAssignmentsController::class, 'enterAssignmentAuditor'])->name('enterAssignmentAuditorURL');
                // Route::post('/assignment/auditor-request/{id?}', [SurveysAssignmentsController::class, 'requestAssignmentAuditor'])->name('requestAssignmentAuditorURL')->where('id', '[0-9]+'); //TODO
                Route::post('/assignment/auditor-revoke/{id?}', [SurveysAssignmentsController::class, 'revokeAssignmentAuditor'])->name('revokeAssignmentAuditorURL')->where('id', '[0-9]+');

                Route::get('/assignment/activities/{subDays?}', [SurveysAssignmentsController::class, 'getRecentActivities'])->name('getRecentActivitiesURL')->where('subDays', '[0-9]+');

            Route::post('/responses/surveyor/store/{id?}', [SurveysResponsesController::class, 'responsesSurveyorStoreOrUpdate'])->name('responsesSurveyorStoreOrUpdateURL');
            Route::post('/responses/auditor/store/{id?}', [SurveysResponsesController::class, 'responsesAuditorStoreOrUpdate'])->name('responsesAuditorStoreOrUpdateURL');

            // Terms Routes
            Route::get('/terms/listing', [SurveysTermsController::class, 'index'])->name('surveysTermsIndexURL');
            Route::get('/terms/create', [SurveysTermsController::class, 'create'])->name('surveysTermsCreateURL');
            Route::get('/terms/form', [SurveysTermsController::class, 'form'])->name('surveysTermsFormURL');
            Route::get('/terms/edit/{id?}', [SurveysTermsController::class, 'edit'])->name('surveysTermsEditURL');
            Route::post('/terms/store/{id?}', [SurveysTermsController::class, 'storeOrUpdate'])->name('surveysTermsStoreOrUpdateURL');
            Route::get('/terms/search', [SurveysTermsController::class, 'search'])->name('surveysTermsSearchURL');

            // Audit Routes
            Route::get('/audits/{id?}', [SurveysAuditController::class, 'index'])->name('surveysAuditIndexURL');


    });

    // Admin Settings
    Route::middleware(['admin'])->group(function () {
        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingsAccountController::class, 'index'])->name('settingsIndexURL');
            Route::get('/account/show/{tab?}', [SettingsAccountController::class, 'show'])->name('settingsAccountShowURL');
                Route::post('/account/store', [SettingsAccountController::class, 'storeOrUpdate'])->name('settingsAccountStoreOrUpdateURL');

            Route::get('/api-keys', [SettingsApiKeysController::class, 'index'])->name('settingsApiKeysURL');

            Route::get('/users', [SettingsUserController::class, 'index'])->name('settingsUsersIndexURL');
            Route::post('/users/store', [SettingsUserController::class, 'store'])->name('settingsUsersStoreURL');
                Route::post('/users/update/{id?}', [SettingsUserController::class, 'update'])->name('settingsUsersUpdateURL');
                Route::get('/users/form/{id?}', [SettingsUserController::class, 'getUserFormContent'])->name('getUserFormContentURL');

            Route::get('/storage', [StorageController::class, 'index'])->name('StorageIndexURL');

            Route::get('/dropbox', [DropboxController::class, 'index'])->name('DropboxIndexURL');
            Route::get('/dropbox/browse/{path}', [DropboxController::class, 'browseFolder'])->name('DropboxBrowseFolderURL');

            Route::post('/clarifai', [ClarifaiImageController::class, 'submit'])->name('ClarifaiSubmitURL');
            Route::post('/scenex', [ScenexImageController::class, 'submit'])->name('ScenexSubmitURL');

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
    });


});

Route::post('/login', [LoginController::class, 'login']);

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::fallback(function () {
    return response()->view('error.auth-404-basic', [], 404);
});

// Catch-All Route
Route::get('{any}', [HomeController::class, 'index'])->where('any', '.*')->name('index');
