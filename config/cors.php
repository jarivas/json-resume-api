<?php

return [
    'paths' => ['*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'], 

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['*'], // Permite que el cliente vea todos los headers

    'max_age' => 0,

    'supports_credentials' => false, // CAMBIA A FALSE si no envías cookies o tokens en Session
];
