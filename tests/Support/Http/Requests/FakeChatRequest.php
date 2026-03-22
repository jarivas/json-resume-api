<?php

namespace Tests\Support\Http\Requests;

use App\Http\Requests\Chat\Chat as ChatRequest;

class FakeChatRequest extends ChatRequest
{
    public function __construct(private array $validatedData)
    {
        parent::__construct();
    }

    public function validated($key = null, $default = null): mixed
    {
        if ($key === null) {
            return $this->validatedData;
        }

        return data_get($this->validatedData, $key, $default);
    }
}
