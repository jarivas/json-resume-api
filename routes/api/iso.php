<?php

use App\Http\Controllers\Iso\Country;
use App\Http\Controllers\Iso\Currency;
use App\Http\Controllers\Iso\Language;
use Illuminate\Support\Facades\Route;

Route::prefix('iso')->group(function () {
    Route::get('country', Country::class);
    Route::get('language', Language::class);
    Route::get('currency', Currency::class);
});
