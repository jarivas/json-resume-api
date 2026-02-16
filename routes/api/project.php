<?php

use App\Http\Controllers\Project\Create;
use App\Http\Controllers\Project\Delete;
use App\Http\Controllers\Project\Read;
use App\Http\Controllers\Project\Update;
use App\Http\Controllers\Project\Attach;
use App\Http\Controllers\Project\Detach;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('project')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{project}', Update::class);
        Route::delete('{project}', Delete::class);
        Route::post('{project}/attach/{basic}', Attach::class);
        Route::post('{project}/detach/{basic}', Detach::class);
    });
