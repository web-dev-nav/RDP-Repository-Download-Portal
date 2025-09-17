#!/bin/bash

# RDP - Repository Download Portal Installation Script
echo "🚀 Installing RDP - Repository Download Portal..."

# Check for required dependencies
command -v php >/dev/null 2>&1 || { echo "❌ PHP is required but not installed. Aborting." >&2; exit 1; }
command -v composer >/dev/null 2>&1 || { echo "❌ Composer is required but not installed. Aborting." >&2; exit 1; }

echo "✅ Dependencies check passed"

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Copy environment file
if [ ! -f .env ]; then
    echo "⚙️ Setting up environment configuration..."
    cp .env.example .env
    php artisan key:generate
else
    echo "✅ Environment file already exists"
fi

# Set up directory permissions
echo "🔐 Setting up directory permissions..."
chmod -R 755 storage bootstrap/cache

# Clear and cache config
echo "🧹 Clearing and caching configuration..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo ""
echo "🎉 Installation completed successfully!"
echo ""
echo "Next steps:"
echo "1. Edit your .env file and add your GitHub token:"
echo "   GITHUB_TOKEN=ghp_your_token_here"
echo "   GITHUB_ADMIN_USER=your-github-username"
echo ""
echo "2. Start the development server:"
echo "   php artisan serve"
echo ""
echo "3. Visit: http://localhost:8000"
echo ""
echo "📚 For detailed setup instructions, see README.md"