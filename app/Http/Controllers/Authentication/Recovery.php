<?php

namespace App\Http\Controllers\Authentication;

use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Helpers\Http\Controllers\Authentication\Login as LoginHelper;

class Recovery
{
    use LoginHelper;

    public function __invoke()
    {
        $user = User::first();

        $data = $this->getToken($user);
        
        Mail::send('mail.recovery', $data, fn ($message) =>
            $message->to($user->email)->subject('Recovery Instructions')
        );

        return response()->noContent();
    }
}