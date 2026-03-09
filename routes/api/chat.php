<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Chat\Chat;

Route::post('chat', Chat::class);
