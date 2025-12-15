<?php

return [
    'paths' => ['*', 'sanctum/csrf-cookie', 'api/*', 'login', 'register', 'logout', 'sanctum/*', 'user'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['*'],
    'max_age' => 0,
    'supports_credentials' => true,
];
