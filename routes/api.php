<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\GroupsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Responses\Response;
use App\Models\User;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Okapi\Filesystem\Filesystem;
use PHPUnit\TextUI\Configuration\GroupCollection;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(AuthController::class)->group(function () {
    Route::post("/login","login");
    Route::post("/register","register");
    Route::post("/refresh","refresh")->middleware("jwt_auth:refresh");
});

Route::middleware("jwt_auth:access")->controller(UserController::class)->group(function (){
    Route::get("/users","index");
});

Route::middleware(['auth:sanctum', 'SuperAdmin'])->controller(UserController::class)->group(function () {
    Route::get('/users', 'getAllUsers'); // Get all users
    Route::post('/users/{user}', 'deleteUser'); // Delete a user with soft delete
    Route::get('/users/{user}/groups', 'getUserGroups'); // Get all groups for a user
});


// groups  // ************************************

Route::middleware(["jwt_auth:access", 'GroupAdmin'])->controller(GroupsController::class)->group(function () {
    Route::post("/update_group/{groupId}","update_group");
    // Route::post("/joinGroup/{id}","joinGroup");
    // Route::get("/groups/{group}/join_requests","getJoinRequests");
    // Route::post("/approveMember/{groupId}/{userId}","approveMember");
    Route::post("/removeUserFromGroup/{groupId}/{userId}","removeUserFromGroup");
});

Route::middleware(['jwt_auth:', 'member_OR_admin'])->controller(GroupsController::class)->group(function () {
    Route::get("/show_group/{groupId}","show_group");
});


Route::middleware('jwt_auth:access')->controller(GroupsController::class)->group(function () {
    Route::post("/store_group","store_group");
    Route::get("/index_group","index_group");

});


Route::middleware(['jwt_auth:access', 'SuperAdmin'])->controller(GroupsController::class)->group(function () {
    Route::get('/groups','getAllGroups');
    Route::post('/groups/{group}/delete_with_files','deleteGroupWithFiles');
    Route::post('/groups/{group}/soft_delete','softDeleteGroup');
});

// files  // *********************************

Route::middleware(['jwt_auth:access', 'member_OR_admin'])->controller(FilesController::class)->group(function () {

    Route::post("/groups/{groupId}/files/check_in","check_in");

    Route::post("/groups/{groupId}/files/check_out","check_out");

    Route::get("/groups/{groupId}/files/{fileId}/versions","getAvailableFilesWithVersions");
});

Route::middleware("jwt_auth:access")->controller(FilesController::class)->group(function () {
    //TODO: add filtering
    Route::get("/groups/{group}/files","index");
    Route::get("/groups/{group}/files/{file}","show");
    //TODO: refactor code and make it take name from file
    Route::post("/groups/{group}/files/","store_file");
    //TODO: done but maybe useless
    Route::post("/groups/{group}/files/{file}","edit");
    //TODO: take by version should be edited
    Route::get("/groups/{group}/files/{file}/download","download");
});

Route::middleware(['jwt_auth:access', 'SuperAdmin'])->controller(FilesController::class)->group(function () {
    Route::get('/groups/{group}/files','getGroupFiles');
    Route::get('/files','getAllFiles');
    Route::post('/files/{file}/delete_with_locks','deleteFileWithLocks');
    Route::post('/files/{file}/soft_delete','softDeleteFile');
});



// notifications  // ***********************************

Route::middleware("jwt_auth:access")->controller(NotificationController::class)->group(function () {
    Route::post("fcm_token","updateDeviceToken");
    Route::post("notify","sendFcmNotification");
});




Route::middleware("jwt_auth:access")->post("/test/{user}",function (Request $request){
    $file = Storage::allFiles("projects_files");
    dump($file);
    return response()->download(storage_path("app/{$file[0]}"),"newfile.txt");
});
