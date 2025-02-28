<?php

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return new UserResource($request->user());
});

Route::group((array)'/admin', function () {
    Route::group((array)'/users', function () {
        Route::get('/', 'UserController@index');
        Route::get('/{user}', 'UserController@show');
//        Route::put('/{user}', 'UserController@update');
        Route::delete('/{user}', 'UserController@destroy');
    });
})->middleware(['auth:sanctum', 'role:admin']);
