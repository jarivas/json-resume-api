<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn ()=> redirect('/up'))->name('health');
