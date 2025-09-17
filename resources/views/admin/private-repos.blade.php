<!DOCTYPE html>
<html>
<head>
    <title>Private Repository Management</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background: #f6f8fa;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .admin-panel {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #24292e;
        }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e4e8;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: #0366d6;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #fafbfc;
            color: #586069;
            border: 1px solid #d1d5da;
        }
        .btn-secondary:hover {
            background: #f3f4f6;
        }
        .btn-danger {
            background: #d73a49;
            color: white;
        }
        .btn-danger:hover {
            background: #cb2431;
        }
        .repo-list {
            margin-top: 30px;
        }
        .repo-item {
            background: #f8f9fa;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #28a745;
        }
        .repo-item .repo-name {
            font-weight: 600;
            color: #24292e;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .user-info {
            background: #f1f8ff;
            padding: 16px 20px;
            border-radius: 8px;
            border-left: 4px solid #0366d6;
            margin-bottom: 20px;
        }
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        .back-link {
            color: #0366d6;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 20px;
            display: inline-block;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <a href="/" class="back-link">← Back to Repository List</a>

    <div class="header">
        <h1>🔒 Private Repository Management</h1>
        <p>Manage private repositories for download access</p>
    </div>

    @if($authenticatedUser)
        <div class="user-info">
            <strong>👤 Authenticated as:</strong> {{ $authenticatedUser['login'] }} ({{ $authenticatedUser['name'] ?? 'No name' }})
        </div>
    @endif

    <div class="admin-panel">
        <h2>Add Private Repository</h2>
        <p>Add a private repository that you have access to. The system will verify your permissions before adding it.</p>

        <div id="alertContainer"></div>

        <form id="addRepoForm">
            <div class="form-group">
                <label for="repo_name">Repository Name</label>
                <input type="text"
                       id="repo_name"
                       name="repo_name"
                       class="form-input"
                       placeholder="e.g., my-private-project"
                       required>
                <small style="color: #586069; margin-top: 4px; display: block;">
                    Enter only the repository name (not the full URL)
                </small>
            </div>

            <button type="submit" class="btn btn-primary" id="addRepoBtn">
                ➕ Add Private Repository
            </button>
        </form>

        <div class="repo-list">
            <h3>Current Private Repositories ({{ count($privateRepos) }})</h3>

            @if(count($privateRepos) > 0)
                @foreach($privateRepos as $repo)
                    <div class="repo-item" data-repo="{{ $repo }}">
                        <div>
                            <div class="repo-name">🔒 {{ $repo }}</div>
                            <small style="color: #586069;">web-dev-nav/{{ $repo }}</small>
                        </div>
                        <button class="btn btn-danger btn-sm" onclick="removeRepository('{{ $repo }}')">
                            🗑️ Remove
                        </button>
                    </div>
                @endforeach
            @else
                <div style="text-align: center; padding: 40px; color: #586069;">
                    <p>No private repositories configured</p>
                    <small>Add repositories using the form above</small>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.getElementById('addRepoForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            const addBtn = document.getElementById('addRepoBtn');
            const originalText = addBtn.innerHTML;

            // Show loading state
            addBtn.innerHTML = '⏳ Adding...';
            addBtn.disabled = true;
            form.classList.add('loading');

            try {
                const response = await fetch('{{ route("admin.add-private-repo") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        repo_name: formData.get('repo_name')
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('success', data.message);
                    form.reset();

                    // Refresh page to show updated list
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert('error', data.message);
                }
            } catch (error) {
                showAlert('error', 'Network error: ' + error.message);
            } finally {
                // Reset button state
                addBtn.innerHTML = originalText;
                addBtn.disabled = false;
                form.classList.remove('loading');
            }
        });

        async function removeRepository(repoName) {
            if (!confirm(`Are you sure you want to remove '${repoName}' from the private repositories list?`)) {
                return;
            }

            try {
                const response = await fetch('{{ route("admin.remove-private-repo") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        repo_name: repoName
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('success', data.message);

                    // Remove from UI
                    const repoItem = document.querySelector(`[data-repo="${repoName}"]`);
                    if (repoItem) {
                        repoItem.remove();
                    }

                    // Refresh after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert('error', data.message);
                }
            } catch (error) {
                showAlert('error', 'Network error: ' + error.message);
            }
        }

        function showAlert(type, message) {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';

            alertContainer.innerHTML = `
                <div class="alert ${alertClass}">
                    ${message}
                </div>
            `;

            // Auto-hide after 5 seconds
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }
    </script>
</body>
</html>