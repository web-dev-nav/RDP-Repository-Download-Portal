<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class DownloadController extends Controller
{
    public function index()
    {
        $repositories = $this->fetchRepositories('web-dev-nav');
        return view('download.index', compact('repositories'));
    }

    private function fetchRepositories($username)
    {
        try {
            $client = new Client();
            $headers = [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'Laravel-App'
            ];

            $allRepos = [];

            // First, get public repositories
            try {
                \Log::info("Fetching public repositories for {$username}...");
                $response = $client->get("https://api.github.com/users/{$username}/repos", [
                    'headers' => array_merge($headers, config('github.token') ? ['Authorization' => 'token ' . config('github.token')] : []),
                    'query' => [
                        'sort' => 'updated',
                        'per_page' => 100,
                        'type' => 'public'
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $publicRepos = json_decode($response->getBody(), true);
                    \Log::info("Public repos for {$username}: " . count($publicRepos));
                    $allRepos = $publicRepos;
                }
            } catch (\Exception $e) {
                \Log::error("Failed to fetch public repos for {$username}: " . $e->getMessage());
            }

            // If we have a token, try to add known private repositories directly
            if (config('github.token')) {
                $knownPrivateRepos = config('private_repos.repositories', []);

                foreach ($knownPrivateRepos as $repoName) {
                    try {
                        \Log::info("Fetching private repository: {$repoName}");
                        $response = $client->get("https://api.github.com/repos/{$username}/{$repoName}", [
                            'headers' => array_merge($headers, ['Authorization' => 'token ' . config('github.token')])
                        ]);

                        if ($response->getStatusCode() === 200) {
                            $privateRepo = json_decode($response->getBody(), true);
                            \Log::info("Successfully fetched private repo: {$repoName}");

                            // Check if already exists in public repos (shouldn't, but just in case)
                            if (!collect($allRepos)->contains('full_name', $privateRepo['full_name'])) {
                                $allRepos[] = $privateRepo;
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error("Failed to fetch private repo {$repoName}: " . $e->getMessage());

                        // If we can't access it, create a placeholder entry for manual download
                        if ($e instanceof \GuzzleHttp\Exception\ClientException && $e->getResponse()->getStatusCode() !== 404) {
                            $allRepos[] = [
                                'name' => $repoName,
                                'full_name' => "{$username}/{$repoName}",
                                'description' => 'Private repository (limited access)',
                                'private' => true,
                                'updated_at' => now()->toISOString(),
                                'language' => 'Unknown',
                                'default_branch' => 'main',
                                'html_url' => "https://github.com/{$username}/{$repoName}",
                                'stargazers_count' => 0,
                                'size' => 0,
                                'placeholder' => true // Mark as placeholder
                            ];
                        }
                    }
                }

                // Also try the /user/repos endpoint as a fallback
                try {
                    \Log::info('Trying /user/repos endpoint...');
                    $response = $client->get('https://api.github.com/user/repos', [
                        'headers' => array_merge($headers, ['Authorization' => 'token ' . config('github.token')]),
                        'query' => [
                            'sort' => 'updated',
                            'per_page' => 100,
                            'type' => 'all',
                            'affiliation' => 'owner,collaborator'
                        ]
                    ]);

                    if ($response->getStatusCode() === 200) {
                        $userRepos = json_decode($response->getBody(), true);
                        \Log::info('User repos endpoint returned: ' . count($userRepos));

                        // Merge any additional repos not already in our list
                        foreach ($userRepos as $repo) {
                            if (!collect($allRepos)->contains('full_name', $repo['full_name'])) {
                                $allRepos[] = $repo;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to fetch from /user/repos: ' . $e->getMessage());
                }
            }

            // Debug: Log the repository count and names
            \Log::info('Total repositories found: ' . count($allRepos));
            \Log::info('Repository names: ' . collect($allRepos)->pluck('name')->implode(', '));
            \Log::info('Private repositories: ' . collect($allRepos)->where('private', true)->pluck('name')->implode(', '));

            // Filter and format repository data
            return collect($allRepos)->map(function ($repo) {
                return [
                    'name' => $repo['name'],
                    'full_name' => $repo['full_name'],
                    'description' => $repo['description'] ?? 'No description available',
                    'private' => $repo['private'] ?? false,
                    'updated_at' => $repo['updated_at'] ?? now()->toISOString(),
                    'language' => $repo['language'] ?? 'Unknown',
                    'default_branch' => $repo['default_branch'] ?? 'main',
                    'html_url' => $repo['html_url'] ?? "https://github.com/{$repo['full_name']}",
                    'stargazers_count' => $repo['stargazers_count'] ?? 0,
                    'size' => $repo['size'] ?? 0,
                    'placeholder' => $repo['placeholder'] ?? false
                ];
            })->sortByDesc('updated_at')->values()->all();

        } catch (\Exception $e) {
            \Log::error('Failed to fetch repositories: ' . $e->getMessage());
            \Log::error('Exception details: ' . $e->getTraceAsString());

            if ($e instanceof \GuzzleHttp\Exception\ClientException) {
                $response = $e->getResponse();
                if ($response) {
                    \Log::error('GitHub API response: ' . $response->getBody());
                }
            }

            return [];
        }
    }

    public function debugRepositories()
    {
        $repositories = $this->fetchRepositories('web-dev-nav');

        // Test token scope by checking user info
        $tokenInfo = null;
        try {
            $client = new Client();
            $response = $client->get('https://api.github.com/user', [
                'headers' => [
                    'Authorization' => 'token ' . config('github.token'),
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'Laravel-App'
                ]
            ]);
            $tokenInfo = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $tokenInfo = ['error' => $e->getMessage()];
        }

        // Test direct access to the private repository
        $privateRepoTest = $this->testPrivateRepoAccess('web-dev-nav', 'api-license-verification');

        // Test the /user/repos endpoint directly
        $userReposTest = $this->testUserReposEndpoint();

        return response()->json([
            'total_count' => count($repositories),
            'private_count' => collect($repositories)->where('private', true)->count(),
            'public_count' => collect($repositories)->where('private', false)->count(),
            'private_repos' => collect($repositories)->where('private', true)->pluck('name')->values(),
            'all_repos' => collect($repositories)->pluck('name')->values(),
            'token_configured' => config('github.token') ? 'Yes' : 'No',
            'token_length' => config('github.token') ? strlen(config('github.token')) : 0,
            'token_first_chars' => config('github.token') ? substr(config('github.token'), 0, 8) . '...' : 'None',
            'authenticated_user' => $tokenInfo,
            'api_rate_limit' => $this->checkRateLimit(),
            'private_repo_direct_test' => $privateRepoTest,
            'user_repos_endpoint_test' => $userReposTest
        ]);
    }

    private function checkRateLimit()
    {
        try {
            $client = new Client();
            $response = $client->get('https://api.github.com/rate_limit', [
                'headers' => [
                    'Authorization' => 'token ' . config('github.token'),
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'Laravel-App'
                ]
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function testPrivateRepoAccess($owner, $repo)
    {
        try {
            $client = new Client();
            $response = $client->get("https://api.github.com/repos/{$owner}/{$repo}", [
                'headers' => [
                    'Authorization' => 'token ' . config('github.token'),
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'Laravel-App'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $repoData = json_decode($response->getBody(), true);
                return [
                    'success' => true,
                    'name' => $repoData['name'],
                    'private' => $repoData['private'],
                    'owner' => $repoData['owner']['login']
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => $e instanceof \GuzzleHttp\Exception\ClientException ? $e->getResponse()->getStatusCode() : 'N/A'
            ];
        }

        return ['success' => false, 'error' => 'Unknown error'];
    }

    private function testUserReposEndpoint()
    {
        try {
            $client = new Client();
            $response = $client->get('https://api.github.com/user/repos', [
                'headers' => [
                    'Authorization' => 'token ' . config('github.token'),
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'Laravel-App'
                ],
                'query' => [
                    'type' => 'all',
                    'sort' => 'updated',
                    'per_page' => 5 // Just get first 5 for testing
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $repos = json_decode($response->getBody(), true);
                return [
                    'success' => true,
                    'total_returned' => count($repos),
                    'repo_names' => collect($repos)->pluck('name')->values(),
                    'private_repos' => collect($repos)->where('private', true)->pluck('name')->values(),
                    'first_repo_details' => count($repos) > 0 ? [
                        'name' => $repos[0]['name'],
                        'private' => $repos[0]['private'],
                        'owner' => $repos[0]['owner']['login']
                    ] : null
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status_code' => $e instanceof \GuzzleHttp\Exception\ClientException ? $e->getResponse()->getStatusCode() : 'N/A'
            ];
        }

        return ['success' => false, 'error' => 'Unknown error'];
    }

    public function adminIndex()
    {
        if (!$this->isAuthorizedAdmin()) {
            return redirect('/')->with('error', 'Unauthorized access');
        }

        $privateRepos = config('private_repos.repositories', []);
        $authenticatedUser = $this->getAuthenticatedGitHubUser();

        return view('admin.private-repos', [
            'privateRepos' => $privateRepos,
            'authenticatedUser' => $authenticatedUser
        ]);
    }

    public function addPrivateRepo(Request $request)
    {
        if (!$this->isAuthorizedAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'repo_name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\-_.]+$/'
        ]);

        $repoName = $request->input('repo_name');

        // Verify repository exists and user has access
        $repoValidation = $this->validatePrivateRepository('web-dev-nav', $repoName);

        if (!$repoValidation['success']) {
            return response()->json([
                'success' => false,
                'message' => $repoValidation['message']
            ]);
        }

        // Add to config
        $currentRepos = config('private_repos.repositories', []);

        if (!in_array($repoName, $currentRepos)) {
            $currentRepos[] = $repoName;
            $this->updatePrivateReposConfig($currentRepos);

            return response()->json([
                'success' => true,
                'message' => "Repository '{$repoName}' added successfully",
                'repository' => $repoValidation['repository']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Repository already exists in the list'
        ]);
    }

    public function removePrivateRepo(Request $request)
    {
        if (!$this->isAuthorizedAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'repo_name' => 'required|string'
        ]);

        $repoName = $request->input('repo_name');
        $currentRepos = config('private_repos.repositories', []);

        if (in_array($repoName, $currentRepos)) {
            $currentRepos = array_values(array_filter($currentRepos, fn($repo) => $repo !== $repoName));
            $this->updatePrivateReposConfig($currentRepos);

            return response()->json([
                'success' => true,
                'message' => "Repository '{$repoName}' removed successfully"
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Repository not found in the list'
        ]);
    }

    private function isAuthorizedAdmin()
    {
        if (!config('private_repos.admin_enabled')) {
            return false;
        }

        $authenticatedUser = $this->getAuthenticatedGitHubUser();
        $allowedUser = config('private_repos.allowed_github_user');

        return $authenticatedUser && $authenticatedUser['login'] === $allowedUser;
    }

    private function getAuthenticatedGitHubUser()
    {
        if (!config('github.token')) {
            return null;
        }

        try {
            $client = new Client();
            $response = $client->get('https://api.github.com/user', [
                'headers' => [
                    'Authorization' => 'token ' . config('github.token'),
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'Laravel-App'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody(), true);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to get authenticated user: ' . $e->getMessage());
        }

        return null;
    }

    private function validatePrivateRepository($owner, $repoName)
    {
        try {
            $client = new Client();
            $response = $client->get("https://api.github.com/repos/{$owner}/{$repoName}", [
                'headers' => [
                    'Authorization' => 'token ' . config('github.token'),
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'Laravel-App'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $repository = json_decode($response->getBody(), true);

                if (!$repository['private']) {
                    return [
                        'success' => false,
                        'message' => 'Repository is public - no need to add manually'
                    ];
                }

                return [
                    'success' => true,
                    'repository' => [
                        'name' => $repository['name'],
                        'description' => $repository['description'],
                        'private' => $repository['private']
                    ]
                ];
            }
        } catch (\Exception $e) {
            if ($e instanceof \GuzzleHttp\Exception\ClientException) {
                $statusCode = $e->getResponse()->getStatusCode();

                if ($statusCode === 404) {
                    return [
                        'success' => false,
                        'message' => 'Repository not found or you do not have access'
                    ];
                } elseif ($statusCode === 403) {
                    return [
                        'success' => false,
                        'message' => 'Access denied - insufficient permissions'
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Error validating repository: ' . $e->getMessage()
            ];
        }

        return [
            'success' => false,
            'message' => 'Unknown error occurred'
        ];
    }

    private function updatePrivateReposConfig($repositories)
    {
        $configPath = config_path('private_repos.php');
        $configContent = "<?php\n\nreturn [\n";
        $configContent .= "    'repositories' => [\n";

        foreach ($repositories as $repo) {
            $configContent .= "        '{$repo}',\n";
        }

        $configContent .= "    ],\n\n";
        $configContent .= "    'admin_enabled' => env('PRIVATE_REPOS_ADMIN', true),\n";
        $configContent .= "    'allowed_github_user' => env('GITHUB_ADMIN_USER', 'web-dev-nav'),\n";
        $configContent .= "];\n";

        file_put_contents($configPath, $configContent);

        // Clear config cache
        if (function_exists('config')) {
            app('config')->set('private_repos.repositories', $repositories);
        }
    }

    public function downloadRepository(Request $request)
    {
        $repoOwner = $request->input('owner', 'web-dev-nav');
        $repoName = $request->input('repo');
        $branch = $request->input('branch', 'main');

        if (!$repoName) {
            return response()->json([
                'success' => false,
                'message' => 'Repository name is required'
            ], 400);
        }

        // For public repos (current implementation)
        if (!config('github.token')) {
            $downloadUrl = "https://github.com/{$repoOwner}/{$repoName}/archive/refs/heads/{$branch}.zip";

            return response()->json([
                'success' => true,
                'download_url' => $downloadUrl,
                'message' => 'Download started successfully!'
            ]);
        }

        // For private repos using GitHub API
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get("https://api.github.com/repos/{$repoOwner}/{$repoName}/zipball/{$branch}", [
                'headers' => [
                    'Authorization' => 'token ' . config('github.token'),
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'Laravel-App'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $filename = "{$repoName}-{$branch}.zip";

                return response($response->getBody())
                    ->header('Content-Type', 'application/zip')
                    ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
                    ->header('Content-Length', $response->getHeader('Content-Length')[0] ?? '');
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download repository: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => false,
            'message' => 'Repository not found or access denied'
        ], 404);
    }
}