# 📦 RDP - Repository Download Portal

> **Professional GitHub Repository Download Manager**

A powerful Laravel-based web application that provides instant download access to your GitHub repositories - both public and private. Built with security, usability, and performance in mind.

<img width="1920" height="4216" alt="captureit_9-16-2025_at_21-27-42" src="https://github.com/user-attachments/assets/e01de529-84ef-473b-b9b9-a1fdb6a5e336" />

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-red.svg" alt="Laravel Version">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue.svg" alt="PHP Version">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
</p>

## ✨ Features

### 🌍 **Repository Management**
- **Public Repositories**: Automatic discovery and listing
- **Private Repositories**: Secure access with GitHub token authentication
- **Real-time Updates**: Live repository information and metadata
- **Batch Operations**: Download multiple repositories efficiently

### 🔍 **Advanced Filtering & Search**
- **Smart Search**: Real-time search across repository names and descriptions
- **Filter by Type**: Separate public and private repositories
- **Language Filter**: Filter by programming language
- **Sort Options**: Sort by name, stars, size, or last updated

### 🔐 **Security & Authentication**
- **GitHub Token Integration**: Secure API access with personal access tokens
- **Owner Authentication**: Only repository owners can manage private repos
- **Admin Panel**: Secure interface for adding/removing private repositories
- **Input Validation**: Comprehensive security checks and validation

### 🎨 **Modern UI/UX**
- **Responsive Design**: Works perfectly on desktop and mobile
- **GitHub-like Interface**: Familiar and intuitive design
- **Real-time Feedback**: Instant download status and progress indicators
- **Dark/Light Friendly**: Optimized for all viewing preferences

## 🚀 Quick Start

### Prerequisites

- **PHP 8.2+**
- **Composer**
- **Node.js & NPM** (for asset compilation)
- **GitHub Personal Access Token** (for private repositories)

### Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/your-username/rdp.git
   cd rdp
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure Environment Variables**
   ```bash
   # Basic Configuration
   APP_NAME="RDP - Repository Download Portal"
   APP_URL=http://localhost:8000

   # GitHub Integration
   GITHUB_TOKEN=ghp_your_personal_access_token_here
   GITHUB_ADMIN_USER=your-github-username

   # Private Repository Management
   PRIVATE_REPOS_ADMIN=true
   ```

5. **Create GitHub Personal Access Token**
   - Go to [GitHub Settings > Developer settings > Personal access tokens](https://github.com/settings/tokens)
   - Click "Generate new token (classic)"
   - Select scopes:
     - ✅ `repo` (for private repositories)
     - ✅ `read:user` (for user information)
     - ✅ `user:email` (for email access)
   - Copy the token and add it to your `.env` file

6. **Run the Application**
   ```bash
   php artisan serve
   ```

   Visit: `http://localhost:8000`

## 📚 Usage Guide

### 🏠 **Main Dashboard**
- Browse all your repositories in a beautiful grid layout
- Use filters to find specific repositories quickly
- Download any repository with a single click

### 🔧 **Admin Panel** (`/admin/private-repos`)
- **Add Private Repositories**: Enter repository name to add it to the download list
- **Remove Repositories**: Manage your private repository collection
- **Access Control**: Only authenticated repository owners can access

### 🔍 **Filtering & Search**
- **Search Box**: Type to search repository names and descriptions
- **Type Filter**: Show only public, private, or all repositories
- **Language Filter**: Filter by programming language (JavaScript, PHP, Python, etc.)
- **Sort Options**: Order by name, update date, stars, or repository size

## ⚙️ Configuration

### GitHub Token Scopes

For **public repositories only**:
- `public_repo` - Access to public repositories

For **private repositories**:
- `repo` - Full control of private repositories
- `read:user` - Read user profile data
- `user:email` - Access user email addresses

### Private Repository Management

Edit `config/private_repos.php` or use the admin panel:

```php
return [
    'repositories' => [
        'api-license-verification',
        'secret-project',
        'internal-tools',
    ],
    'admin_enabled' => env('PRIVATE_REPOS_ADMIN', true),
    'allowed_github_user' => env('GITHUB_ADMIN_USER', 'your-username'),
];
```

## 🛡️ Security Features

### Authentication & Authorization
- **Token-based Authentication**: Secure GitHub API integration
- **Owner Verification**: Only repository owners can manage private repos
- **Input Validation**: Comprehensive security checks on all inputs
- **CSRF Protection**: Laravel's built-in CSRF protection

### Repository Access Control
- **Permission Verification**: Validates access before adding repositories
- **Existence Checks**: Ensures repositories exist before adding
- **Privacy Validation**: Only allows private repositories in admin panel

## 🔧 Advanced Configuration

### Custom Repository Sources

To fetch repositories from different GitHub users or organizations, modify the controller:

```php
// In DownloadController.php
public function index()
{
    $repositories = $this->fetchRepositories('your-organization');
    return view('download.index', compact('repositories'));
}
```

### Rate Limiting

GitHub API has rate limits:
- **Without token**: 60 requests/hour
- **With token**: 5,000 requests/hour

The application automatically handles rate limiting and provides informative error messages.

## 🧪 Testing & Development

### Run Tests
```bash
php artisan test
```

### Debug Mode
```bash
# Enable debug mode
APP_DEBUG=true

# Visit debug endpoint
http://localhost:8000/debug/repositories
```

### Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 📁 Project Structure

```
rdp/
├── app/Http/Controllers/
│   └── DownloadController.php     # Main application logic
├── config/
│   ├── github.php                 # GitHub integration config
│   └── private_repos.php          # Private repository config
├── resources/views/
│   ├── download/
│   │   └── index.blade.php        # Main repository dashboard
│   └── admin/
│       └── private-repos.blade.php # Admin panel for private repos
└── routes/
    └── web.php                    # Application routes
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📜 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Built with [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- GitHub API integration for repository access
- Inspired by the need for secure repository management

---

<p align="center">
  <strong>⭐ If you found this project helpful, please give it a star!</strong>
</p>
