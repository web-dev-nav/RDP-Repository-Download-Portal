@echo off
REM RDP - Repository Download Portal Installation Script for Windows

echo 🚀 Installing RDP - Repository Download Portal...

REM Check for PHP
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ PHP is required but not installed. Please install PHP and try again.
    pause
    exit /b 1
)

REM Check for Composer
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Composer is required but not installed. Please install Composer and try again.
    pause
    exit /b 1
)

echo ✅ Dependencies check passed

REM Install PHP dependencies
echo 📦 Installing PHP dependencies...
composer install --no-dev --optimize-autoloader

REM Copy environment file
if not exist .env (
    echo ⚙️ Setting up environment configuration...
    copy .env.example .env
    php artisan key:generate
) else (
    echo ✅ Environment file already exists
)

REM Clear and cache config
echo 🧹 Clearing and caching configuration...
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo.
echo 🎉 Installation completed successfully!
echo.
echo Next steps:
echo 1. Edit your .env file and add your GitHub token:
echo    GITHUB_TOKEN=ghp_your_token_here
echo    GITHUB_ADMIN_USER=your-github-username
echo.
echo 2. Start the development server:
echo    php artisan serve
echo.
echo 3. Visit: http://localhost:8000
echo.
echo 📚 For detailed setup instructions, see README.md
pause