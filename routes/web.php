<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/', function () {
    return redirect('/admin'); // опционально
});
require __DIR__.'/auth.php';
