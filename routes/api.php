<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\GroupsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Repositories\FileRepository;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

Route::middleware(['jwt_auth:access'])->controller(UserController::class)->group(function () {
    Route::get('/users', 'getAllUsers');
    Route::post('/users/{user}', 'deleteUser');
    Route::get('/users/{user}/groups', 'getUserGroups');
    Route::get("/groups/{group}/users","indexPerGroup"); 
});


// groups  // ************************************

Route::middleware(["jwt_auth:access", 'GroupAdmin'])->controller(GroupsController::class)->group(function () {
    Route::post("/update_group/{group}","update_group");
    // Route::post("/joinGroup/{id}","joinGroup");
    // Route::get("/groups/{group}/join_requests","getJoinRequests");
    // Route::post("/approveMember/{group}/{userId}","approveMember");
    Route::post("/groups/{group}/users/{user}","removeUserFromGroup");
});

Route::middleware(['jwt_auth:access', 'member_OR_admin'])->controller(GroupsController::class)->group(function () {
    Route::get("/show_group/{group}","show_group");
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
    Route::get("/groups/{group}/files","indexPerGroup");
    Route::get("/groups/{group}/files/{file}","show");
    Route::get("/groups/{group}/files/{file}/versions","getAvailableFilesWithVersions");
    Route::get("/groups/{group}/files/{file}/download","download");
    Route::post("/groups/{group}/files/","store_file");
    Route::post("/groups/{group}/files/check_in","check_in");
    Route::post("/groups/{group}/files/check_out","check_out");
});

Route::middleware(['jwt_auth:access', 'SuperAdmin'])->controller(FilesController::class)->group(function () {
    Route::get('/files','getAllFiles');
    Route::post('/files/{file}/delete_with_locks','deleteFileWithLocks');
    Route::post('/files/{file}/soft_delete','softDeleteFile');
});


// notifications  // ***********************************

Route::middleware("jwt_auth:access")->controller(NotificationController::class)->group(function () {
    Route::post("fcm_token","updateDeviceToken");
    Route::post("notify","sendFcmNotification");
});




Route::middleware("jwt_auth:access")->post("/test/{user}",function (Request $request) {
    fopen(storage_path("app/fake.txt"),"w");
    File::withTrashed()->where("name","like","file%")->forceDelete();
    return collect(["amro", "khaled", "mousab"]);
});
