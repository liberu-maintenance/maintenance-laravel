# Liberu — Maintenance (CMMS)

[![Install](https://github.com/liberu-maintenance/maintenance-laravel/actions/workflows/install.yml/badge.svg)](https://github.com/liberu-maintenance/maintenance-laravel/actions/workflows/install.yml)
[![Tests](https://github.com/liberu-maintenance/maintenance-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/liberu-maintenance/maintenance-laravel/actions/workflows/tests.yml)
[![Docker](https://github.com/liberu-maintenance/maintenance-laravel/actions/workflows/main.yml/badge.svg)](https://github.com/liberu-maintenance/maintenance-laravel/actions/workflows/main.yml)
[![Codecov](https://codecov.io/gh/liberu-maintenance/maintenance-laravel/branch/main/graph/badge.svg)](https://codecov.io/gh/liberu-maintenance/maintenance-laravel)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A lightweight, open-source Computerised Maintenance Management System (CMMS) built with Laravel, Livewire and Filament. Designed for teams to track equipment, schedules, work orders and notifications.

Key technologies: PHP · Laravel · Filament · Livewire · Jetstream

---

## Quick start

Requirements:
- PHP 8.3+ and Composer
- MySQL (or configure your preferred DB in `.env`)

Recommended (one-time):

```bash
# from project root (Unix-like shells)
./setup.sh
```

Or run the essential steps manually:

```bash
composer install
cp .env.example .env   # or restore your existing .env
php artisan key:generate
php artisan migrate --seed
```

Notes:
- The `setup.sh` script may overwrite `.env` (it prompts before doing so).
- Seeders run by default; skip if you don't want sample data.

---

## Docker

Build and run the image locally:

```bash
docker build -t maintenance-laravel .
docker run -p 8000:8000 maintenance-laravel
```

Or use Laravel Sail (recommended for local development):

```bash
./vendor/bin/sail up
```

---

## Short description

Liberu Maintenance provides:

- Equipment, Work Orders and Maintenance Schedules
- Team & user management (Jetstream + Teams)
- Notifications and assignment workflows
- Custom forms and checklists

This repository focuses on a modular architecture so it can be extended and integrated with other Liberu projects.

---

## Related projects

| Project | Repository |
|---|---|
| Accounting | https://github.com/liberu-accounting/accounting-laravel |
| Automation | https://github.com/liberu-automation/automation-laravel |
| Billing | https://github.com/liberu-billing/billing-laravel |
| Boilerplate | https://github.com/liberusoftware/boilerplate |
| Browser Game | https://github.com/liberu-browser-game/browser-game-laravel |
| CMS | https://github.com/liberu-cms/cms-laravel |
| Control Panel | https://github.com/liberu-control-panel/control-panel-laravel |
| CRM | https://github.com/liberu-crm/crm-laravel |
| E-commerce | https://github.com/liberu-ecommerce/ecommerce-laravel |
| Genealogy | https://github.com/liberu-genealogy/genealogy-laravel |
| Maintenance (this) | https://github.com/liberu-maintenance/maintenance-laravel |
| Real Estate | https://github.com/liberu-real-estate/real-estate-laravel |
| Social Network | https://github.com/liberu-social-network/social-network-laravel |

---

## Contributing

Contributions welcome — please open issues and pull requests. For larger changes, discuss via an issue first so we can coordinate.

## License

This project is licensed under the MIT License. See the `LICENSE` file for details.

---

## Contributors

<a href="https://github.com/liberu-maintenance/maintenance-laravel/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=liberu-maintenance/maintenance-laravel" alt="contributors"/>
</a>
