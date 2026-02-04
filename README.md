# Liberu — Maintenance (CMMS)

[![Install](https://github.com/liberu-maintenance/maintenance-laravel/actions/workflows/install.yml/badge.svg)](https://github.com/liberu-maintenance/maintenance-laravel/actions/workflows/install.yml) 
[![Tests](https://github.com/liberu-maintenance/maintenance-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/liberu-maintenance/maintenance-laravel/actions/workflows/tests.yml) 
[![Docker](https://github.com/liberu-maintenance/maintenance-laravel/actions/workflows/main.yml/badge.svg)](https://github.com/liberu-maintenance/maintenance-laravel/actions/workflows/main.yml) 
[![Codecov](https://codecov.io/gh/liberu-maintenance/maintenance-laravel/branch/main/graph/badge.svg)](https://codecov.io/gh/liberu-maintenance/maintenance-laravel)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A lightweight, open-source Computerised Maintenance Management System (CMMS) built with Laravel, Livewire and Filament. Designed to help teams manage equipment, maintenance schedules, work orders, tasks and notifications.

Key technologies: PHP · Laravel · Filament · Livewire · Jetstream

---

## Quick start

Minimum requirements
- PHP ^8.5
- Composer
- A database (MySQL, Postgres, etc.)

Recommended (one-time)

```bash
# from project root (Unix-like shells)
./setup.sh
```

Manual steps

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

Notes
- `setup.sh` may prompt and overwrite `.env` — review before accepting.
- Seeders install sample data; skip them if you prefer an empty DB.

---

## Docker / Local dev

Build and run the image locally:

```bash
docker build -t maintenance-laravel .
docker run -p 8000:8000 maintenance-laravel
```

Or use Laravel Sail for local development:

```bash
./vendor/bin/sail up
```

---

## What it provides

- Equipment inventory and status
- Maintenance schedules and one-off maintenance
- Work orders, tasks and assignment workflows
- Team and user management (Jetstream + Teams)
- Notifications, reports and custom forms

The codebase uses a modular architecture to make extensions and integrations straightforward.

---

## Related projects

| Project | Repository |
|---|---:|
| Accounting | [liberu-accounting/accounting-laravel](https://github.com/liberu-accounting/accounting-laravel) |
| Automation | [liberu-automation/automation-laravel](https://github.com/liberu-automation/automation-laravel) |
| Billing | [liberu-billing/billing-laravel](https://github.com/liberu-billing/billing-laravel) |
| Boilerplate | [liberusoftware/boilerplate](https://github.com/liberusoftware/boilerplate) |
| Browser Game | [liberu-browser-game/browser-game-laravel](https://github.com/liberu-browser-game/browser-game-laravel) |
| CMS | [liberu-cms/cms-laravel](https://github.com/liberu-cms/cms-laravel) |
| Control Panel | [liberu-control-panel/control-panel-laravel](https://github.com/liberu-control-panel/control-panel-laravel) |
| CRM | [liberu-crm/crm-laravel](https://github.com/liberu-crm/crm-laravel) |
| E-commerce | [liberu-ecommerce/ecommerce-laravel](https://github.com/liberu-ecommerce/ecommerce-laravel) |
| Genealogy | [liberu-genealogy/genealogy-laravel](https://github.com/liberu-genealogy/genealogy-laravel) |
| Maintenance (this) | [liberu-maintenance/maintenance-laravel](https://github.com/liberu-maintenance/maintenance-laravel) |
| Real Estate | [liberu-real-estate/real-estate-laravel](https://github.com/liberu-real-estate/real-estate-laravel) |
| Social Network | [liberu-social-network/social-network-laravel](https://github.com/liberu-social-network/social-network-laravel) |

---

## Contributing

Contributions welcome. Open an issue to propose larger changes before submitting a pull request so we can coordinate.

## License

MIT — see the `LICENSE` file for details.

---

## Contributors

<a href="https://github.com/liberu-maintenance/maintenance-laravel/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=liberu-maintenance/maintenance-laravel" alt="contributors"/>
</a>
