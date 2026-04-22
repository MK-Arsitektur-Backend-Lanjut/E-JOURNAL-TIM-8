<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/module1', function () {
    return view('module1');
});

Route::get('/module2', function () {
    return view('module2');
});
