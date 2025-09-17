<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GitHub Personal Access Token
    |--------------------------------------------------------------------------
    |
    | Your GitHub Personal Access Token for accessing private repositories.
    | Set this in your .env file as GITHUB_TOKEN=your_token_here
    |
    | To create a token:
    | 1. Go to GitHub Settings > Developer settings > Personal access tokens
    | 2. Generate new token with 'repo' scope for private repos
    | 3. Add GITHUB_TOKEN=your_token to .env file
    |
    */

    'token' => env('GITHUB_TOKEN', null),
];