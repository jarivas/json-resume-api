<?php

namespace App\Services\ResumeImport;

class ResolvedResumeSource
{
    public function __construct(
        public string $type,
        public string $source,
        public ?string $localPath = null,
        public ?string $textContent = null,
        public bool $deleteLocalPathOnCleanup = false,
    ) {}
}
