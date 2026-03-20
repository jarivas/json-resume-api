<?php

use App\Http\Controllers\Chat\Chat;
use Illuminate\Support\Facades\Route;

Route::post('chat', Chat::class);
