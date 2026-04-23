<?php

use App\Http\Controllers\AgentConversation\Read;
use App\Http\Controllers\AgentConversation\ReadMessages;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('agent-conversation')
    ->group(function () {
        Route::get('{page?}', Read::class)->whereNumber('page');
        Route::get('{conversation}/messages/{page?}', ReadMessages::class)->whereNumber('page');
    });
