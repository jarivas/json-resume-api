<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Requests\Authentication\ChangePassword as Request;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ChangePassword
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new HttpException(404, 'Not found.');
        }

        $user->password = $request->input('password');
        $user->save();

        return response()->noContent();
    }
}
