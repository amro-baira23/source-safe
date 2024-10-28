<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
    Route::post("/login/{user}","login");
    Route::post("/register","register");

});



Route::controller(GroupsController::class)->group(function () {
    Route::post("/store_group","store_group");
    Route::get("/show_group/{id}","show_group");
    Route::get("/index_group","index_group");
    Route::post("/update_group/{id}","update_group");
    Route::post("/joinGroup/{id}","joinGroup");
    Route::post("/approveMember/{groupId}/{userId}","approveMember");

});


Route::middleware("auth:sanctum")->post("/test",function (Request $request){

});
