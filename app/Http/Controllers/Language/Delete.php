<?php

namespace App\Http\Controllers\Language;

use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\Language;

class Delete
{
    public function __invoke(Language $language)
    {
        if (! $language->delete()) {
            throw new HttpException(400, 'Problem deleting the Language model');
        }

        return response()->noContent();
    }
}
