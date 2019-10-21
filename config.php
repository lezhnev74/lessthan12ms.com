<?php
return [
    // Your environment: local, production, staging
    'env' => env('APP_ENV', 'local'),
    // Base URL of your website (used in URL generation)
    'base_url' => env('BASE_URL', 'http://localhost'),
    // Folder to look for posts
    'texts_path' => env('TEXTS_PATH', base_path() . DIRECTORY_SEPARATOR . 'texts'),
    // Folder for the web root
    'webroot_path' => env('WEB_PATH', base_path() . DIRECTORY_SEPARATOR . 'web'),
];