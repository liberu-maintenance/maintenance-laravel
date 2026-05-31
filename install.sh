#!/usr/bin/env bash
# Installation script for Liberu Maintenance.
# Provides Standalone, Docker, and Kubernetes installation options.
# For advanced setups (Kubernetes), use setup.sh.

set -euo pipefail

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; RESET='\033[0m'

print_error()   { echo -e "${RED}❌  ERROR: $*${RESET}" >&2; }
print_success() { echo -e "${GREEN}✅  $*${RESET}"; }
print_info()    { echo -e "${BLUE}ℹ️   $*${RESET}"; }
print_warning() { echo -e "${YELLOW}⚠️   $*${RESET}"; }
print_header()  { echo ""; echo "══════════════════════════════════"; echo "  $1"; echo "══════════════════════════════════"; echo ""; }

command_exists() { command -v "$1" >/dev/null 2>&1; }

COMPOSER_CMD=""

ensure_composer() {
    if command_exists composer; then
        COMPOSER_CMD="composer"; print_success "Composer found"; return 0
    fi
    if [ -f "composer.phar" ]; then
        COMPOSER_CMD="php composer.phar"; print_success "composer.phar found"; return 0
    fi
    if ! command_exists php; then print_error "PHP required"; exit 1; fi
    if ! command_exists curl; then print_error "curl required to download Composer"; exit 1; fi
    print_info "Downloading Composer..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet
    php -r "unlink('composer-setup.php');" 2>/dev/null || true
    [ -f "composer.phar" ] || { print_error "Composer download failed"; exit 1; }
    COMPOSER_CMD="php composer.phar"
    print_success "Composer downloaded"
}

install_composer_deps() {
    print_header "PHP Dependencies"
    ensure_composer
    if [ -d "vendor" ] && [ -f "vendor/autoload.php" ]; then
        read -rp "  vendor/ exists. Reinstall? [y/N] " r
        [[ "${r,,}" == "y" ]] || { print_success "Skipped"; return 0; }
    fi
    ${COMPOSER_CMD} install --no-interaction --prefer-dist
    print_success "Composer dependencies installed"
}

install_npm_deps() {
    print_header "JavaScript Dependencies"
    if ! command_exists npm; then
        print_warning "npm not found — skipping. Install Node.js from https://nodejs.org"
        return 0
    fi
    if [ -d "node_modules" ]; then
        read -rp "  node_modules/ exists. Reinstall? [y/N] " r
        [[ "${r,,}" == "y" ]] || { print_success "Skipped"; return 0; }
    fi
    npm ci || npm install
    print_success "npm dependencies installed"
}

build_assets() {
    print_header "Frontend Assets"
    command_exists npm || { print_warning "npm not found — skipping build"; return 0; }
    npm run build
    print_success "Frontend assets built"
}

docker_compose_cmd() {
    if command_exists docker-compose; then docker-compose "$@"; else docker compose "$@"; fi
}

install_standalone() {
    print_header "STANDALONE INSTALLATION"
    command_exists php || { print_error "PHP required"; exit 1; }

    if [ ! -f ".env" ]; then
        cp .env.example .env
        print_success ".env created from .env.example"
        print_warning "Edit .env with your database credentials then press Enter."
        read -rp "..."
    else
        print_info "Using existing .env"
    fi

    while true; do
        read -rp "  Database credentials configured? [y/N] " c
        case "${c,,}" in
            y) break ;;
            n|"") print_warning "Set up .env then re-run install.sh"; exit 0 ;;
        esac
    done

    install_composer_deps
    install_npm_deps
    build_assets

    php artisan key:generate
    print_success "App key generated"

    read -rp "  Fresh migration (drops all data)? [y/N] " fresh
    if [[ "${fresh,,}" == "y" ]]; then
        php artisan migrate:fresh --force
    else
        php artisan migrate --force
    fi
    print_success "Database migrated"

    read -rp "  Seed database? [y/N] " seed
    [[ "${seed,,}" == "y" ]] && { php artisan db:seed; print_success "Database seeded"; }

    print_header "Tests"
    if [ -f "vendor/bin/phpunit" ]; then
        set +e; php artisan test; set -e
    else
        print_warning "PHPUnit not in vendor/bin — skipping"
    fi

    php artisan optimize:clear
    php artisan config:cache
    php artisan route:cache
    print_success "═══════ Installation complete ════════"

    read -rp "  Start dev server? [y/N] " serve
    [[ "${serve,,}" == "y" ]] && php artisan serve
}

install_docker() {
    print_header "DOCKER INSTALLATION"
    command_exists docker || { print_error "Docker not found. See https://docs.docker.com/get-docker/"; exit 1; }
    print_success "Docker available"
    docker_compose_cmd version >/dev/null 2>&1 || { print_error "Docker Compose not found"; exit 1; }
    print_success "Docker Compose available"

    if [ ! -f ".env" ]; then
        cp .env.example .env
        print_warning "Edit .env with Docker credentials then press Enter."
        read -rp "..."
    fi

    docker_compose_cmd up -d --build
    print_success "Containers started — http://localhost:${APP_PORT:-8000}"
}

install_kubernetes() {
    print_header "KUBERNETES INSTALLATION"
    command_exists kubectl || { print_error "kubectl not found. See https://kubernetes.io/docs/tasks/tools/"; exit 1; }

    local k8s_dir=""
    for d in k8s kubernetes; do [ -d "${d}" ] && { k8s_dir="${d}"; break; }; done
    [ -n "${k8s_dir}" ] || { print_error "No k8s/ or kubernetes/ directory found."; exit 1; }

    if grep -q "REPLACE_WITH" "${k8s_dir}/secret.yaml" 2>/dev/null; then
        print_warning "secret.yaml has placeholder values. Update before deploying."
        read -rp "  Apply anyway? [y/N] " f
        [[ "${f,,}" == "y" ]] || { print_info "Aborted"; exit 0; }
    fi

    if [ -f "${k8s_dir}/kustomization.yaml" ]; then
        kubectl apply -k "${k8s_dir}/"
    else
        kubectl apply -f "${k8s_dir}/"
    fi
    print_success "Kubernetes resources applied"
    print_info "kubectl get all -n liberu-maintenance"
}

main() {
    clear
    print_header "LIBERU MAINTENANCE — INSTALLER"
    echo "  1) Standalone  (local / bare-metal)"
    echo "  2) Docker      (containerised)"
    echo "  3) Kubernetes  (k8s cluster)"
    echo "  4) Exit"
    echo ""

    while true; do
        read -rp "Choice [1-4]: " choice
        case "${choice}" in
            1) install_standalone; break ;;
            2) install_docker;     break ;;
            3) install_kubernetes; break ;;
            4) print_info "Cancelled"; exit 0 ;;
            *) print_warning "Enter 1–4" ;;
        esac
    done
}

main
