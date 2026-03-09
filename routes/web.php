<?php

use App\Http\Controllers\Basic\Read;
use App\Http\Controllers\Basic\ReadOne;
use Illuminate\Support\Facades\Route;

Route::get('/', fn ()=> redirect('/up'))
    ->name('health');

Route::get('/basic', Read::class);

Route::get('/basic/{id}', ReadOne::class)
    ->whereUlid('id');