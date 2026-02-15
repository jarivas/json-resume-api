<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class Logout extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();

        $user->tokens()->delete();

        return response()->noContent();
    }
}