#!/usr/bin/env bash
# Setup script for the Liberu Maintenance project.
# Provides installation options for Standalone, Docker, or Kubernetes deployments.

set -euo pipefail

# ─── Colors ───────────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RESET='\033[0m'

print_error()   { echo -e "${RED}❌  ERROR: $*${RESET}" >&2; }
print_success() { echo -e "${GREEN}✅  $*${RESET}"; }
print_info()    { echo -e "${BLUE}ℹ️   $*${RESET}"; }
print_warning() { echo -e "${YELLOW}⚠️   $*${RESET}"; }
print_header()  { echo ""; echo "══════════════════════════════════════"; echo "  $1"; echo "══════════════════════════════════════"; echo ""; }

command_exists() { command -v "$1" >/dev/null 2>&1; }

require_php() {
    if ! command_exists php; then
        print_error "PHP is required. Install PHP 8.5+ from https://www.php.net/downloads"
        exit 1
    fi
    local version
    version=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
    print_success "PHP ${version} found"
}

# ─── Composer ─────────────────────────────────────────────────────────────────
COMPOSER_CMD=""

ensure_composer() {
    if command_exists composer; then
        COMPOSER_CMD="composer"
        print_success "Composer $(composer --version --no-ansi 2>/dev/null | head -1) found"
        return 0
    fi
    if [ -f "composer.phar" ]; then
        COMPOSER_CMD="php composer.phar"
        print_success "composer.phar found"
        return 0
    fi

    print_warning "Composer not found. Downloading composer.phar..."
    require_php

    if ! command_exists curl; then
        print_error "curl is required to download Composer. Install curl or Composer manually."
        exit 1
    fi

    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" || {
        print_error "Failed to download Composer installer."
        exit 1
    }
    php composer-setup.php --quiet
    php -r "unlink('composer-setup.php');" 2>/dev/null || true

    if [ -f "composer.phar" ]; then
        COMPOSER_CMD="php composer.phar"
        print_success "Composer downloaded successfully"
    else
        print_error "Failed to install Composer"
        exit 1
    fi
}

install_composer_deps() {
    print_header "PHP Dependencies (Composer)"
    ensure_composer

    if [ -d "vendor" ] && [ -f "vendor/autoload.php" ]; then
        print_info "vendor/ exists."
        read -rp "  Reinstall? [y/N] " reply
        [[ "${reply,,}" == "y" ]] || { print_success "Skipped"; return 0; }
    fi

    print_info "Running: ${COMPOSER_CMD} install --no-interaction --prefer-dist"
    ${COMPOSER_CMD} install --no-interaction --prefer-dist
    print_success "Composer dependencies installed"
}

# ─── NPM ──────────────────────────────────────────────────────────────────────
install_npm_deps() {
    print_header "JavaScript Dependencies (npm)"

    if ! command_exists npm; then
        print_warning "npm not found — skipping JS install. Install Node.js from https://nodejs.org"
        return 0
    fi
    print_success "npm $(npm --version) found"

    if [ -d "node_modules" ]; then
        print_info "node_modules/ exists."
        read -rp "  Reinstall? [y/N] " reply
        [[ "${reply,,}" == "y" ]] || { print_success "Skipped"; return 0; }
    fi

    npm ci || npm install
    print_success "npm dependencies installed"
}

build_assets() {
    print_header "Frontend Assets (npm run build)"
    if ! command_exists npm; then
        print_warning "npm not found — skipping asset build"
        return 0
    fi
    npm run build
    print_success "Frontend assets built"
}

# ─── Standalone ───────────────────────────────────────────────────────────────
install_standalone() {
    print_header "STANDALONE INSTALLATION"
    require_php

    echo "  User : $(whoami)"
    echo "  PHP  : $(php -r 'echo PHP_VERSION;')"
    echo ""

    # .env setup
    if [ ! -f ".env" ]; then
        cp .env.example .env
        print_success ".env.example → .env"
        print_warning "Edit .env with your database credentials before continuing."
        read -rp "Press Enter once .env is configured..."
    else
        print_info ".env already exists — using existing file"
    fi

    # Confirm DB credentials
    while true; do
        read -rp "  Database credentials configured in .env? [y/N] " confirm
        case "${confirm,,}" in
            y) break ;;
            n|"") print_warning "Configure .env then re-run setup.sh"; exit 0 ;;
        esac
    done

    install_composer_deps
    install_npm_deps
    build_assets

    print_header "Generate App Key"
    php artisan key:generate
    print_success "Application key generated"

    print_header "Database Migration"
    local migrate_cmd="migrate --force"
    read -rp "  Fresh migration (drops all tables)? [y/N] " fresh
    [[ "${fresh,,}" == "y" ]] && migrate_cmd="migrate:fresh --force"
    php artisan ${migrate_cmd}
    print_success "Database migrated"

    print_header "Database Seeding"
    read -rp "  Seed database? [y/N] " seed
    if [[ "${seed,,}" == "y" ]]; then
        php artisan db:seed
        print_success "Database seeded"
    fi

    print_header "PHPUnit Tests"
    if [ -f "vendor/bin/phpunit" ]; then
        # Run tests without failing the install script
        set +e
        php artisan test
        local test_exit=$?
        set -e
        if [ "${test_exit}" -eq 0 ]; then
            print_success "All tests passed"
        else
            print_warning "Some tests failed (exit ${test_exit}). Review output above."
        fi
    else
        print_warning "PHPUnit not found in vendor/bin/ — skipping"
    fi

    print_header "Cache Optimization"
    php artisan optimize:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    print_success "Caches warmed"

    print_success "═════════���════════ Installation complete ══════════════════"
    echo ""

    read -rp "  Start dev server (php artisan serve)? [y/N] " serve
    [[ "${serve,,}" == "y" ]] && php artisan serve
}

# ─── Docker ───────────────────────────────────────────────────────────────────
docker_compose() {
    if command_exists docker-compose; then
        docker-compose "$@"
    else
        docker compose "$@"
    fi
}

install_docker() {
    print_header "DOCKER INSTALLATION"

    if ! command_exists docker; then
        print_error "Docker is not installed. See https://docs.docker.com/get-docker/"
        exit 1
    fi
    print_success "Docker $(docker --version | grep -oE '[0-9]+\.[0-9]+\.[0-9]+')"

    if ! docker_compose version >/dev/null 2>&1; then
        print_error "Docker Compose not found. See https://docs.docker.com/compose/install/"
        exit 1
    fi
    print_success "Docker Compose available"

    if [ ! -f ".env" ]; then
        cp .env.example .env
        print_success ".env.example → .env"
        print_warning "Edit .env to configure your Docker environment (DB passwords, APP_KEY, etc.)"
        read -rp "Press Enter once .env is configured..."
    else
        print_info "Using existing .env"
    fi

    print_info "Building and starting containers (this may take a few minutes)..."
    docker_compose up -d --build

    print_success "Containers started. Application: http://localhost:${APP_PORT:-8000}"
    echo ""
    print_info "Useful commands:"
    print_info "  docker compose logs -f app    — follow app logs"
    print_info "  docker compose exec app bash  — shell into app container"
    print_info "  docker compose down           — stop containers"
}

# ─── Kubernetes ───────────────────────────────────────────────────────────────
install_kubernetes() {
    print_header "KUBERNETES INSTALLATION"

    if ! command_exists kubectl; then
        print_error "kubectl not found. See https://kubernetes.io/docs/tasks/tools/"
        exit 1
    fi
    print_success "kubectl $(kubectl version --client --short 2>/dev/null | head -1)"

    local k8s_dir=""
    for dir in k8s kubernetes; do
        if [ -d "${dir}" ]; then
            k8s_dir="${dir}"
            break
        fi
    done

    if [ -z "${k8s_dir}" ]; then
        print_error "No k8s/ or kubernetes/ directory found."
        print_info "Run 'mkdir k8s' and add your manifests, or use the included k8s/ directory."
        exit 1
    fi
    print_info "Using manifests from: ${k8s_dir}/"

    # Secret validation
    if grep -q "REPLACE_WITH" "${k8s_dir}/secret.yaml" 2>/dev/null; then
        print_warning "k8s/secret.yaml contains placeholder values (REPLACE_WITH_*)."
        print_warning "Edit k8s/secret.yaml with real values before deploying."
        read -rp "  Apply anyway? [y/N] " force
        [[ "${force,,}" == "y" ]] || { print_info "Aborting. Update secret.yaml and re-run."; exit 0; }
    fi

    if [ -f "${k8s_dir}/kustomization.yaml" ]; then
        print_info "Applying via kustomize..."
        kubectl apply -k "${k8s_dir}/"
    else
        print_info "Applying manifests..."
        kubectl apply -f "${k8s_dir}/"
    fi

    print_success "Kubernetes resources applied"
    print_info "Check status: kubectl get all -n liberu-maintenance"
    print_info "View logs:    kubectl logs -n liberu-maintenance -l app=liberu-maintenance-app -f"
}

# ─── Main ─────────────────────────────────────────────────────────────────────
main() {
    clear
    print_header "LIBERU MAINTENANCE — INSTALLER"

    echo "  Select installation type:"
    echo ""
    echo "    1) Standalone  (local / bare-metal)"
    echo "    2) Docker      (containerised)"
    echo "    3) Kubernetes  (k8s cluster)"
    echo "    4) Exit"
    echo ""

    while true; do
        read -rp "Choice [1-4]: " choice
        case "${choice}" in
            1) install_standalone; break ;;
            2) install_docker;     break ;;
            3) install_kubernetes; break ;;
            4) print_info "Cancelled"; exit 0 ;;
            *) print_warning "Enter 1, 2, 3, or 4" ;;
        esac
    done
}

main
