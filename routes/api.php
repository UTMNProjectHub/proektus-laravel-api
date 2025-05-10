<?php

use App\Http\Controllers\User\UserController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return new UserResource(Auth::user()->load('roles'));
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

Route::controller(UserController::class)->group(function () {
    Route::get('/users/search', 'search');
});

Route::prefix('/file')->group(function () {
    Route::controller(\App\Http\Controllers\Project\ProjectFileController::class)->group(function () {
        Route::post('/upload', 'upload');
        Route::delete('/delete', 'destroy');
        Route::get('/download/{file_id}', 'download');
    })->middleware(['auth:sanctum']);
});



Route::prefix('/projects')->group(function () {
    Route::controller(App\Http\Controllers\Project\ProjectController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::post('/', 'store')->middleware(['auth:sanctum']); // there needs to be a middleware that controls access to proj creation
        Route::put('/{id}', 'update')->middleware(['auth:sanctum']);
        Route::delete('/{id}', 'destroy')->middleware(['auth:sanctum']);
    });

    Route::controller(App\Http\Controllers\Project\ProjectFileController::class)->group(function () {
        Route::get('/{id}/files', 'index');
        Route::post('/{id}/files', 'upload');
        Route::delete('/{id}/files', 'destroy');
    });

    Route::controller(\App\Http\Controllers\Project\ProjectUserController::class)->group(function () {
        Route::get('/{project_id}/users', 'index')->middleware(['auth:sanctum']);
        Route::post('/{project_id}/users', 'store')->middleware(['auth:sanctum']);
        Route::delete('/{project_id}/users/{user_id}', 'destroy')->middleware(['auth:sanctum']);
    });
});
