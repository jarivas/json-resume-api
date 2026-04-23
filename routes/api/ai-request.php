<?php

use App\Http\Controllers\AiRequest\Read;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('ai-request')
    ->group(function () {
        Route::get('{page?}', Read::class)->whereNumber('page');
    });
