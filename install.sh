#!/bin/bash
# Installation script for the maintenance-laravel project.
#
# This script provides installation options for Standalone, Docker, or Kubernetes deployments.
# It handles composer and npm installations with fallback logic and error checking.

set -e  # Exit on error

# Colors for output
RED='\e[91m'
GREEN='\e[92m'
YELLOW='\e[93m'
BLUE='\e[94m'
RESET='\e[39m'

# Function to print colored messages
print_message() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${RESET}"
}

print_header() {
    echo ""
    echo "=================================="
    echo "$1"
    echo "=================================="
    echo ""
}

print_error() {
    print_message "$RED" "❌ ERROR: $1"
}

print_success() {
    print_message "$GREEN" "✅ $1"
}

print_info() {
    print_message "$BLUE" "ℹ️  $1"
}

print_warning() {
    print_message "$YELLOW" "⚠️  $1"
}

# Check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Download composer.phar if composer is not available
ensure_composer() {
    if command_exists composer; then
        print_success "Composer is already installed"
        COMPOSER_CMD="composer"
        return 0
    fi

    print_warning "Composer command not found. Attempting to download composer.phar..."

    if ! command_exists curl; then
        print_error "curl is required to download composer. Please install curl or composer manually."
        return 1
    fi

    if ! command_exists php; then
        print_error "PHP is required. Please install PHP first."
        return 1
    fi

    print_info "Downloading Composer installer..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

    print_info "Installing Composer locally..."
    php composer-setup.php --quiet

    php -r "unlink('composer-setup.php');"

    if [ -f "composer.phar" ]; then
        print_success "Composer.phar downloaded successfully"
        COMPOSER_CMD="php composer.phar"
        return 0
    else
        print_error "Failed to download composer.phar"
        return 1
    fi
}

# Install composer dependencies
install_composer_dependencies() {
    print_header "🎬 COMPOSER INSTALL"

    if [ -d "vendor" ] && [ -f "vendor/autoload.php" ]; then
        print_info "Vendor directory already exists. Skipping composer install."
        read -p "Do you want to reinstall composer dependencies? (y/n) " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_success "Skipping composer install"
            return 0
        fi
    fi

    if ! ensure_composer; then
        print_error "Cannot proceed without Composer"
        return 1
    fi

    print_info "Running: $COMPOSER_CMD install"
    if eval "$COMPOSER_CMD install --no-interaction --prefer-dist"; then
        print_success "Composer dependencies installed successfully"
        return 0
    else
        print_error "Composer install failed"
        return 1
    fi
}

# Install npm dependencies
install_npm_dependencies() {
    print_header "🎬 NPM INSTALL"

    if [ -d "node_modules" ]; then
        print_info "node_modules directory already exists. Skipping npm install."
        read -p "Do you want to reinstall npm dependencies? (y/n) " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_success "Skipping npm install"
            return 0
        fi
    fi

    if ! command_exists npm; then
        print_error "npm is not installed. Please install Node.js and npm first."
        print_info "Visit: https://nodejs.org/"
        return 1
    fi

    print_info "Running: npm install"
    if npm install; then
        print_success "NPM dependencies installed successfully"
        return 0
    else
        print_error "NPM install failed"
        return 1
    fi
}

# Build frontend assets
build_frontend_assets() {
    print_header "🎬 NPM BUILD"

    if ! command_exists npm; then
        print_error "npm is not installed. Cannot build assets."
        return 1
    fi

    print_info "Running: npm run build"
    if npm run build; then
        print_success "Frontend assets built successfully"
        return 0
    else
        print_error "NPM build failed"
        return 1
    fi
}

# Standalone installation
install_standalone() {
    print_header "STANDALONE INSTALLATION"
    print_info "Starting standalone installation process..."

    clear
    echo "=================================="
    echo "===== USER: [$(whoami)]"
    echo "===== [PHP $(php -r 'echo phpversion();')]"
    echo "=================================="
    echo ""

    copy=true
    while true; do
        read -p "🎬 DEV ---> DID YOU WANT TO COPY THE .ENV.EXAMPLE TO .ENV? (y/n) " yn
        case $yn in
            [Yy]* )
                print_success "Copying .env.example to .env"
                cp .env.example .env
                copy=true
                break
                ;;
            [Nn]* )
                print_success "Continuing with your .env configuration"
                copy=false
                break
                ;;
            * )
                print_warning "Please answer yes or no."
                ;;
        esac
    done

    echo ""
    echo "=================================="
    echo ""

    if [ "$copy" = true ]; then
        while true; do
            read -p "🎬 DEV ---> DID YOU SETUP YOUR DATABASE CREDENTIALS IN THE .ENV FILE? (y/n) " cond
            case $cond in
                [Yy]* )
                    print_success "Perfect let's continue with the setup"
                    break
                    ;;
                [Nn]* )
                    print_warning "Please setup your .env file and run this script again"
                    exit 0
                    ;;
                * )
                    print_warning "Please answer yes or no."
                    ;;
            esac
        done
    fi

    echo ""
    echo "=================================="
    echo ""

    if ! install_composer_dependencies; then
        print_error "Installation failed at composer install step"
        exit 1
    fi

    echo ""
    echo "=================================="
    echo ""

    if ! install_npm_dependencies; then
        print_warning "NPM install failed, but continuing..."
    fi

    echo ""
    echo "=================================="
    echo ""

    if ! build_frontend_assets; then
        print_warning "NPM build failed, but continuing..."
    fi

    echo ""
    echo "=================================="
    echo ""

    print_header "🎬 PHP ARTISAN KEY:GENERATE"
    if php artisan key:generate; then
        print_success "Application key generated"
    else
        print_error "Failed to generate application key"
        exit 1
    fi

    echo ""
    echo "=================================="
    echo ""

    print_header "🎬 PHP ARTISAN MIGRATE:FRESH"
    if php artisan migrate:fresh; then
        print_success "Database migrated successfully"
    else
        print_error "Database migration failed"
        exit 1
    fi

    echo ""
    echo "=================================="
    echo ""

    print_header "🎬 PHP ARTISAN DB:SEED"
    if php artisan db:seed; then
        print_success "Database seeded successfully"
    else
        print_error "Database seeding failed"
        exit 1
    fi

    echo ""
    echo "=================================="
    echo ""

    print_header "🎬 RUNNING PHPUNIT TESTS"
    if [ -f "vendor/bin/phpunit" ]; then
        if ./vendor/bin/phpunit; then
            print_success "PHPUnit tests passed"
        else
            print_warning "PHPUnit tests failed. Please review the errors."
        fi
    else
        print_warning "PHPUnit not found. Skipping tests."
    fi

    echo ""
    echo "=================================="
    echo ""

    print_header "🎬 PHP ARTISAN OPTIMIZE:CLEAR"
    php artisan optimize:clear
    php artisan route:clear

    echo ""
    print_success "=================================="
    print_success "============== DONE =============="
    print_success "=================================="
    echo ""

    while true; do
        read -p "🎬 DEV ---> DID YOU WANT TO START THE SERVER? (y/n) " cond
        case $cond in
            [Yy]* )
                print_success "Starting server..."
                php artisan serve
                break
                ;;
            [Nn]* )
                print_success "Installation complete. You can start the server later with: php artisan serve"
                exit 0
                ;;
            * )
                print_warning "Please answer yes or no."
                ;;
        esac
    done
}

# Docker installation
install_docker() {
    print_header "DOCKER INSTALLATION"
    print_info "Starting Docker installation process..."

    if ! command_exists docker; then
        print_error "Docker is not installed. Please install Docker first."
        print_info "Visit: https://docs.docker.com/get-docker/"
        exit 1
    fi

    print_success "Docker is installed"

    if ! command_exists docker-compose && ! docker compose version >/dev/null 2>&1; then
        print_error "Docker Compose is not installed. Please install Docker Compose first."
        print_info "Visit: https://docs.docker.com/compose/install/"
        exit 1
    fi

    print_success "Docker Compose is available"

    if [ ! -f ".env" ]; then
        print_info "Copying .env.example to .env"
        cp .env.example .env
        print_warning "Please edit .env file to configure your Docker environment"
        read -p "Press Enter to continue after editing .env..."
    fi

    print_info "Building and starting Docker containers..."
    if command_exists docker-compose; then
        docker-compose up -d --build
    else
        docker compose up -d --build
    fi

    if [ $? -eq 0 ]; then
        print_success "Docker containers started successfully"
        print_info "Your application should be available at http://localhost:8000"
    else
        print_error "Failed to start Docker containers"
        exit 1
    fi
}

# Main installation menu
main() {
    clear
    print_header "MAINTENANCE-LARAVEL INSTALLER"

    echo "Please select installation type:"
    echo ""
    echo "  1) Standalone (Local development/production)"
    echo "  2) Docker (Containerized deployment)"
    echo "  3) Exit"
    echo ""

    while true; do
        read -p "Enter your choice (1-3): " choice
        case $choice in
            1)
                install_standalone
                break
                ;;
            2)
                install_docker
                break
                ;;
            3)
                print_info "Installation cancelled"
                exit 0
                ;;
            *)
                print_warning "Invalid choice. Please enter 1, 2, or 3."
                ;;
        esac
    done
}

# Run main function
main
