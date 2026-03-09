<?php

namespace App\Http\Controllers\Authentication;

use Illuminate\Support\Facades\Auth;

class Logout
{
    public function __invoke()
    {
        $user = Auth::user();

        $user->tokens()->delete();

        return response()->noContent();
    }
}