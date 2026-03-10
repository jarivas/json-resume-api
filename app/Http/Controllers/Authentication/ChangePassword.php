<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Requests\Authentication\ChangePassword as Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\User;

class ChangePassword
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new HttpException(404, 'Not found.');
        }

        $user->password = $request->input("password");
        $user->save();

        return response()->noContent();
    }
}