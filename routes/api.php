<?php

use App\Http\Controllers\User\AdminUserController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return new UserResource(Auth::user()->load('roles'));
});

//Route::group((array)'/admin', function () {
//    Route::group((array)'/users', function () {
//        Route::get('/', 'AdminUserController@index');
//        Route::get('/{user}', 'AdminUserController@show');
////        Route::put('/{user}', 'AdminUserController@update');
//        Route::delete('/{user}', 'AdminUserController@destroy');
//    });
//})->middleware(['auth:sanctum', 'role:admin']);

Route::prefix('/admin')->group(function () {
    Route::controller(\App\Http\Controllers\User\AdminUserController::class)->group(function () {
        Route::get('/users/', 'index');
        Route::get('/users/{user}', 'show');
        Route::delete('users/{user}', 'destroy');
    });
})->middleware(['auth:sanctum', 'role:admin']);

Route::controller(AdminUserController::class)->group(function () {
    Route::get('/users/search', 'search');
});

Route::prefix('/file')->group(function () {
    Route::controller(\App\Http\Controllers\Project\ProjectFileController::class)->group(function () {
        Route::post('/upload', 'upload');
        Route::delete('/delete', 'destroy');
        Route::get('/download/{file_id}', 'download');
    })->middleware(['auth:sanctum']);
});

Route::prefix('/profile')->group(function () {
    Route::controller(\App\Http\Controllers\User\UserController::class)->group(function () {
        Route::get('/', 'show');
        Route::put('/', 'update');
    })->middleware(['auth:sanctum']);
});



Route::prefix('/projects')->group(function () {
    Route::controller(App\Http\Controllers\Project\ProjectController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::post('/', 'store')->middleware(['auth:sanctum']);
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
        Route::put('/{project_id}/users/{user_id}', 'update')->middleware(['auth:sanctum']);
        Route::delete('/{project_id}/users/{user_id}', 'destroy')->middleware(['auth:sanctum']);
    });

    Route::controller(\App\Http\Controllers\ProjectReadmeController::class)->group(function () {
        Route::get('/{project_id}/readme', 'index');
        Route::put('/{project_id}/readme', 'update')->middleware(['auth:sanctum']);
    });
});
