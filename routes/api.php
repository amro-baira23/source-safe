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

// groups  //

Route::middleware(['jwt_auth:access', 'GroupAdmin'])->controller(GroupsController::class)->group(function () {
    Route::post("/update_group/{id}","update_group");
    // Route::post("/joinGroup/{id}","joinGroup");
    // Route::get("/groups/{group}/join_requests","getJoinRequests");
    // Route::post("/approveMember/{groupId}/{userId}","approveMember");
    Route::post("/removeUserFromGroup/{groupId}/{userId}","removeUserFromGroup");
});

Route::middleware(['jwt_auth:access', 'member_OR_admin'])->controller(GroupsController::class)->group(function () {
    Route::get("/show_group/{id}","show_group");
});


Route::middleware('jwt_auth:access')->controller(GroupsController::class)->group(function () {
    Route::post("/store_group","store_group");
    Route::get("/index_group","index_group");

});

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

Route::middleware("jwt_auth:access")->controller(NotificationController::class)->group(function () {
    Route::post("fcm_token","updateDeviceToken");
    Route::post("notify","sendFcmNotification");
});

Route::middleware("jwt_auth:access")->post("/test/{user}",function (Request $request){    
    return "hello world";
});
