<?php

namespace App\Http\Controllers\Language;

use App\Http\Controllers\Controller;
use App\Models\Language;

class Delete extends Controller
{
    public function __invoke(Language $language)
    {
        return $language->delete() ? response()->noContent() :
            $this->getErrorResponse('error', 'Problem deleting the Language model');
    }
}
