<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Private Repository Configuration
    |--------------------------------------------------------------------------
    |
    | List of private repositories that should be accessible for download.
    | Only the authenticated GitHub user (repository owner) can modify this list.
    |
    */

    'repositories' => [
        'api-license-verification',
        // Add more private repository names here
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Authentication
    |--------------------------------------------------------------------------
    |
    | Security settings for private repository management
    |
    */

    'admin_enabled' => env('PRIVATE_REPOS_ADMIN', true),
    'allowed_github_user' => env('GITHUB_ADMIN_USER', 'web-dev-nav'),
];