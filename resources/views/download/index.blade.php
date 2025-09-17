<!DOCTYPE html>
<html>
<head>
    <title>GitHub Repository Downloads - web-dev-nav</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f6f8fa;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .repos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 20px;
        }
        .repo-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 4px solid #0366d6;
        }
        .repo-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        .repo-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .repo-name {
            font-size: 18px;
            font-weight: 600;
            color: #0366d6;
            text-decoration: none;
            margin: 0;
        }
        .repo-name:hover {
            text-decoration: underline;
        }
        .repo-badge {
            background: #ffeaa7;
            color: #fdcb6e;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .repo-badge.private {
            background: #ff7675;
            color: white;
        }
        .repo-badge.public {
            background: #00b894;
            color: white;
        }
        .repo-description {
            color: #586069;
            margin-bottom: 16px;
            line-height: 1.4;
        }
        .repo-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
            font-size: 14px;
            color: #586069;
        }
        .download-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
            width: 100%;
        }
        .download-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .download-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .stats {
            text-align: center;
            margin-bottom: 20px;
            color: #586069;
        }
        .language-dot {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 4px;
        }
        .no-repos {
            text-align: center;
            padding: 60px 20px;
            color: #586069;
        }
        .filters {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .filters-row {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: #586069;
            text-transform: uppercase;
        }
        .filter-input {
            padding: 8px 12px;
            border: 2px solid #e1e4e8;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        .filter-input:focus {
            outline: none;
            border-color: #0366d6;
        }
        .filter-select {
            padding: 8px 12px;
            border: 2px solid #e1e4e8;
            border-radius: 6px;
            font-size: 14px;
            background: white;
            cursor: pointer;
        }
        .clear-filters {
            background: #fafbfc;
            border: 1px solid #d1d5da;
            color: #586069;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }
        .clear-filters:hover {
            background: #f3f4f6;
            border-color: #c6cbd1;
        }
        .results-count {
            margin-left: auto;
            color: #586069;
            font-size: 14px;
            font-weight: 500;
        }
        .repo-card.hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🐙 GitHub Repository Downloads</h1>
        <p>Download repositories from <strong>web-dev-nav</strong></p>
        @if(count($repositories) > 0)
            <div class="stats">
                Found {{ count($repositories) }} repositories
                @if(config('private_repos.admin_enabled'))
                    <a href="{{ route('admin.private-repos') }}" style="margin-left: 20px; color: #0366d6; text-decoration: none; font-weight: 500;">
                        🔧 Manage Private Repos
                    </a>
                @endif
            </div>
        @endif
    </div>

    @if(count($repositories) > 0)
        <div class="filters">
            <div class="filters-row">
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text"
                           id="searchFilter"
                           class="filter-input"
                           placeholder="Search repositories..."
                           style="width: 250px;">
                </div>

                <div class="filter-group">
                    <label>Type</label>
                    <select id="typeFilter" class="filter-select">
                        <option value="all">All Repositories</option>
                        <option value="public">🌍 Public Only</option>
                        <option value="private">🔒 Private Only</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Language</label>
                    <select id="languageFilter" class="filter-select">
                        <option value="all">All Languages</option>
                        @php
                            $languages = collect($repositories)->pluck('language')->filter()->unique()->sort()->values();
                        @endphp
                        @foreach($languages as $language)
                            <option value="{{ $language }}">{{ $language }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label>Sort by</label>
                    <select id="sortFilter" class="filter-select">
                        <option value="updated">Recently Updated</option>
                        <option value="name">Name A-Z</option>
                        <option value="name_desc">Name Z-A</option>
                        <option value="stars">Most Stars</option>
                        <option value="size">Largest Size</option>
                        <option value="size_desc">Smallest Size</option>
                    </select>
                </div>

                <button class="clear-filters" onclick="clearAllFilters()">
                    🗑️ Clear Filters
                </button>

                <div class="results-count" id="resultsCount">
                    Showing {{ count($repositories) }} repositories
                </div>
            </div>
        </div>

        <div class="repos-grid" id="reposGrid">
            @foreach($repositories as $repo)
                <div class="repo-card"
                     data-name="{{ strtolower($repo['name']) }}"
                     data-description="{{ strtolower($repo['description'] ?? '') }}"
                     data-type="{{ $repo['private'] ? 'private' : 'public' }}"
                     data-language="{{ $repo['language'] ?? 'Unknown' }}"
                     data-stars="{{ $repo['stargazers_count'] ?? 0 }}"
                     data-size="{{ $repo['size'] ?? 0 }}"
                     data-updated="{{ $repo['updated_at'] }}">
                    <div class="repo-header">
                        <a href="{{ $repo['html_url'] }}" target="_blank" class="repo-name">
                            {{ $repo['name'] }}
                        </a>
                        <span class="repo-badge {{ $repo['private'] ? 'private' : 'public' }}">
                            {{ $repo['private'] ? '🔒 Private' : '🌍 Public' }}
                        </span>
                    </div>

                    <p class="repo-description">
                        {{ $repo['description'] }}
                    </p>

                    <div class="repo-meta">
                        @if($repo['language'])
                            <span>
                                <span class="language-dot" style="background-color: #f1e05a;"></span>
                                {{ $repo['language'] }}
                            </span>
                        @endif
                        @if($repo['stargazers_count'] > 0)
                            <span>⭐ {{ $repo['stargazers_count'] }}</span>
                        @endif
                        <span>📦 {{ number_format($repo['size']) }} KB</span>
                        <span>🕒 {{ \Carbon\Carbon::parse($repo['updated_at'])->diffForHumans() }}</span>
                    </div>

                    <button class="download-btn"
                            onclick="downloadRepository('{{ $repo['name'] }}', '{{ $repo['default_branch'] }}', this)">
                        📥 Download {{ $repo['name'] }} ({{ $repo['default_branch'] }})
                    </button>
                </div>
            @endforeach
        </div>
    @else
        <div class="no-repos">
            <h3>No repositories found</h3>
            <p>Unable to fetch repositories from GitHub. This might be because:</p>
            <ul style="text-align: left; display: inline-block;">
                <li>GitHub API rate limit reached</li>
                <li>Network connectivity issues</li>
                <li>Invalid GitHub username</li>
            </ul>
        </div>
    @endif

    <script>
    async function downloadRepository(repoName, branch, btn) {
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '⏳ Downloading...';

        try {
            const response = await fetch('{{ route("download.repository") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    repo: repoName,
                    branch: branch,
                    owner: 'web-dev-nav'
                })
            });

            // Check if response is JSON or file download
            const contentType = response.headers.get('content-type');

            if (contentType && contentType.includes('application/json')) {
                // Handle JSON response (public repo redirect)
                const data = await response.json();

                if (data.success) {
                    // Create temporary download link
                    const link = document.createElement('a');
                    link.href = data.download_url;
                    link.download = `${repoName}.zip`;
                    link.style.display = 'none';

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    showSuccess(btn, originalText);
                } else {
                    throw new Error(data.message || 'Download failed');
                }
            } else if (contentType && contentType.includes('application/zip')) {
                // Handle direct file download (private repo)
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);

                const link = document.createElement('a');
                link.href = url;
                link.download = `${repoName}.zip`;
                link.style.display = 'none';

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                window.URL.revokeObjectURL(url);
                showSuccess(btn, originalText);
            } else {
                throw new Error('Unexpected response format');
            }

        } catch (error) {
            console.error('Download failed:', error);
            showError(btn, originalText, error.message);
        }
    }

    function showSuccess(btn, originalText) {
        btn.innerHTML = '✅ Downloaded!';
        btn.style.background = '#28a745';

        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            btn.disabled = false;
        }, 3000);
    }

    function showError(btn, originalText, message) {
        btn.innerHTML = '❌ Failed';
        btn.style.background = '#dc3545';

        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            btn.disabled = false;
        }, 3000);

        console.error('Download error:', message);
    }

    // Repository filtering and sorting functionality
    let allRepos = [];

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize repository data
        const repoCards = document.querySelectorAll('.repo-card');
        console.log('Found repository cards:', repoCards.length);

        allRepos = Array.from(repoCards).map(card => ({
            element: card,
            name: card.dataset.name || '',
            description: card.dataset.description || '',
            type: card.dataset.type || 'public',
            language: card.dataset.language || 'Unknown',
            stars: parseInt(card.dataset.stars) || 0,
            size: parseInt(card.dataset.size) || 0,
            updated: new Date(card.dataset.updated)
        }));

        console.log('Initialized repositories:', allRepos.length);

        // Add event listeners
        const searchFilter = document.getElementById('searchFilter');
        const typeFilter = document.getElementById('typeFilter');
        const languageFilter = document.getElementById('languageFilter');
        const sortFilter = document.getElementById('sortFilter');

        if (searchFilter) searchFilter.addEventListener('input', applyFilters);
        if (typeFilter) typeFilter.addEventListener('change', applyFilters);
        if (languageFilter) languageFilter.addEventListener('change', applyFilters);
        if (sortFilter) sortFilter.addEventListener('change', applyFilters);

        // Initial display
        updateResultsCount(allRepos.length);
    });

    function applyFilters() {
        if (allRepos.length === 0) {
            console.log('No repositories loaded yet');
            return;
        }

        const searchTerm = document.getElementById('searchFilter')?.value.toLowerCase() || '';
        const typeFilter = document.getElementById('typeFilter')?.value || 'all';
        const languageFilter = document.getElementById('languageFilter')?.value || 'all';
        const sortFilter = document.getElementById('sortFilter')?.value || 'updated';

        console.log('Applying filters:', { searchTerm, typeFilter, languageFilter, sortFilter });

        // Filter repositories
        let filteredRepos = allRepos.filter(repo => {
            // Search filter
            const matchesSearch = !searchTerm ||
                repo.name.includes(searchTerm) ||
                (repo.description && repo.description.includes(searchTerm));

            // Type filter
            const matchesType = typeFilter === 'all' || repo.type === typeFilter;

            // Language filter
            const matchesLanguage = languageFilter === 'all' ||
                repo.language === languageFilter ||
                (languageFilter === 'Unknown' && (!repo.language || repo.language === 'Unknown'));

            return matchesSearch && matchesType && matchesLanguage;
        });

        // Sort repositories
        filteredRepos.sort((a, b) => {
            switch (sortFilter) {
                case 'name':
                    return a.name.localeCompare(b.name);
                case 'name_desc':
                    return b.name.localeCompare(a.name);
                case 'stars':
                    return b.stars - a.stars;
                case 'size':
                    return b.size - a.size;
                case 'size_desc':
                    return a.size - b.size;
                case 'updated':
                default:
                    return b.updated - a.updated;
            }
        });

        console.log('Filtered repositories:', filteredRepos.length, 'of', allRepos.length);

        // Update display
        updateRepoDisplay(filteredRepos);
        updateResultsCount(filteredRepos.length);
    }

    function updateRepoDisplay(filteredRepos) {
        const grid = document.getElementById('reposGrid');

        // Hide all repositories
        allRepos.forEach(repo => {
            repo.element.classList.add('hidden');
        });

        // Show filtered repositories in order
        filteredRepos.forEach((repo, index) => {
            repo.element.classList.remove('hidden');
            repo.element.style.order = index;
        });
    }

    function updateResultsCount(count) {
        const totalCount = allRepos.length;
        const countElement = document.getElementById('resultsCount');

        if (count === totalCount) {
            countElement.textContent = `Showing ${count} repositories`;
        } else {
            countElement.textContent = `Showing ${count} of ${totalCount} repositories`;
        }
    }

    function clearAllFilters() {
        document.getElementById('searchFilter').value = '';
        document.getElementById('typeFilter').value = 'all';
        document.getElementById('languageFilter').value = 'all';
        document.getElementById('sortFilter').value = 'updated';
        applyFilters();
    }
    </script>
</body>
</html>