<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\GroupsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Models\Group;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware("LoggingAspect")
    ->controller(AuthController::class)->group(function () {
        Route::post("/login","login");
        Route::post("/register","register");

        Route::middleware("jwt_auth:refresh")
            ->post("/refresh","refresh");
});

Route::middleware(['jwt_auth:access','LoggingAspect'])
    ->controller(UserController::class)->group(function () {
        Route::get('/users', 'index');

        Route::middleware('AuthAspect:admin')->group(function() {
            Route::get('/users/{user}/groups', 'getGroups');
            Route::post('/users/{user}', 'remove');
        });

        Route::middleware('AuthAspect:member')->group(function() {
            Route::get("/groups/{group}/users","indexPerGroup");
        });

        Route::middleware('AuthAspect:adminGroup')->group(function() {
            Route::post("/groups/{group}/users/{user}","removeFromGroup");
            Route::get("/groups/{group}/users/{user}/operations","getOperations");
            Route::get("/groups/{group}/users/{user}/operations/csv","getOperationsAsCSV");
            Route::get("/groups/{group}/users/{user}/operations/pdf","getOperationsAsPDF");
        });
});

Route::middleware(['jwt_auth:access',"LoggingAspect"])
    ->controller(GroupsController::class)->group(function () {
        Route::post("/store_group","store_group");
        Route::get("/index_group","index_group");

        Route::middleware('AuthAspect:admin',)->group(function() {
            Route::get('/groups','getAllGroups');
            Route::post('/groups/{group}/delete_with_files','deleteGroupWithFiles');
            Route::post('/groups/{group}/soft_delete','softDeleteGroup');
        });

        Route::middleware('AuthAspect:member')->group(function() {
            Route::get("/show_group/{group}","show_group");
        });

        Route::middleware('AuthAspect:adminGroup')->group(function() {
            Route::post("/update_group/{group}","update_group");
        });
});

Route::middleware(['jwt_auth:access',"LoggingAspect"])
    ->controller(FilesController::class)->group(function () {

        Route::middleware('AuthAspect:admin')->group(function() {
            Route::get('/files','getAllFiles');
            Route::post('/files/{file}/delete_with_locks','deleteFileWithLocks');
            Route::post('/files/{file}/soft_delete','softDeleteFile');
        });

        Route::middleware('AuthAspect:adminGroup')->group(function() {
            Route::post('/groups/{group}/files/{file}/activate','activation');
        });

        Route::middleware('AuthAspect:member')->group(function() {
            Route::get("/groups/{group}/files","indexPerGroup");
            Route::get("/groups/{group}/files/{file}","show");
            Route::get("/groups/{group}/files/{file}/versions","getAvailableFilesWithVersions");
            Route::get("/groups/{group}/files/{file}/download","download");
            Route::post("/groups/{group}/files/","store");
            Route::post("/groups/{group}/files/check_in","checkIn")
                ->middleware("event-aspect:check-in");
            Route::post("/groups/{group}/files/check_out","checkOut")
                ->middleware("event-aspect:check-in");
            Route::get("/groups/{group}/files/{file}/operations","getOperations");
            Route::get("/groups/{group}/files/{file}/operations/csv","getOperationsAsCSV");
            Route::get("/groups/{group}/files/{file}/operations/pdf","getOperationsAsPDF");
        });
});

Route::middleware("jwt_auth:access")
    ->controller(NotificationController::class)->group(function () {
        Route::post("fcm_token","updateDeviceToken");
        Route::post("notify","sendFcmNotification");
});

Route::middleware("jwt_auth:access")->post("/test/{group}",function (Group $group) {
    // SendNotificationToUsersJob::dispatchSync($group->users()->whereNot("user_id",1)->get());   
    return "hello world";
});
