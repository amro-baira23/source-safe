<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\GroupsController;
use App\Http\Responses\Response;
use App\Models\User;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
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

});



Route::middleware("auth:sanctum")->controller(GroupsController::class)->group(function () {
    Route::post("/store_group","store_group");
    Route::get("/show_group/{id}","show_group");
    Route::get("/index_group","index_group");
    Route::post("/update_group/{id}","update_group");
    Route::post("/joinGroup/{id}","joinGroup");
    Route::get("/groups/{group}/join_requests","getJoinRequests");
    Route::post("/approveMember/{groupId}/{userId}","approveMember");
    Route::post("/removeUserFromGroup/{groupId}/{userId}","removeUserFromGroup");

});
Route::middleware("auth:sanctum")->controller(FilesController::class)->group(function () {
    Route::get("/groups/{group}/files","index");
    Route::get("/groups/{group}/files/{file}","show");
    Route::post("/groups/{group}/files/","store_file");
    Route::post("/groups/{group}/files/{file}","edit");
    Route::get("/groups/{group}/files/{file}/download","download");
});


Route::middleware("auth:sanctum")->post("/test/{user}",function (Request $request){
    $file = Storage::allFiles("project_files");
    return response()->download(storage_path("app/{$file[0]}"),"newfile.txt");
});