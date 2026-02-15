<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\ChangePassword as Request;

class ChangePassword extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $user->password = $request->input("password");
        $user->save();

        return response()->noContent();
    }
}