<?php

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['status' => 'ok']);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return new UserResource($request->user());
});

//Route::group((array)'/admin', function () {
//    Route::group((array)'/users', function () {
//        Route::get('/', 'UserController@index');
//        Route::get('/{user}', 'UserController@show');
////        Route::put('/{user}', 'UserController@update');
//        Route::delete('/{user}', 'UserController@destroy');
//    });
//})->middleware(['auth:sanctum', 'role:admin']);

Route::prefix('/admin')->group(function () {
    Route::controller(\App\Http\Controllers\User\UserController::class)->group(function () {
        Route::get('/users/', 'index');
        Route::get('/users/{user}', 'show');
        Route::delete('users/{user}', 'destroy');
    });
})->middleware(['auth:sanctum', 'role:admin']);

Route::prefix('/file')->group(function () {
    Route::controller(\App\Http\Controllers\Project\ProjectFileController::class)->group(function () {
        Route::post('/upload', 'upload');
    });
});

Route::controller(App\Http\Controllers\Project\ProjectController::class)->group(function () {
    Route::get('/projects', 'index');
    Route::get('/projects/{id}', 'show');
    Route::post('/projects', 'store');
    Route::put('/projects/{id}', 'update');
    Route::delete('/projects/{id}', 'destroy');
});
