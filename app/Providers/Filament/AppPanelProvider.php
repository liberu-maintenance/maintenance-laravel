<?php

namespace App\Providers\Filament;

use App\Filament\App\Pages;
use App\Filament\App\Pages\EditProfile;
use App\Http\Middleware\TeamsPermission;
use App\Listeners\CreatePersonalTeam;
use App\Listeners\SwitchTeam;
use App\Models\Team;
use Filament\Events\Auth\Registered;
use Filament\Events\TenantSet;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Event;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Jetstream;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel
            ->default()
            ->id('app')
            ->path('app')
            ->login([AuthenticatedSessionController::class, 'create'])
            ->registration()
            ->passwordReset()
            ->emailVerification()
            ->viteTheme('resources/css/filament/app/theme.css')
            ->colors([
                'primary' => Color::Teal,
                'secondary' => Color::Emerald,
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger' => Color::Red,
                'info' => Color::Sky,
                'gray' => Color::Slate,
            ])
            ->brandName('Liberu Maintenance')
            ->brandLogo(asset('images/logo.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('images/favicon.ico'))
            ->navigationGroups([
                'Dashboard' => [
                    'label' => 'Dashboard',
                    'icon' => 'heroicon-o-home',
                    'collapsed' => false,
                ],
                'Work Management' => [
                    'label' => 'Work Management',
                    'icon' => 'heroicon-o-clipboard-document-list',
                    'collapsed' => false,
                ],
                'Maintenance' => [
                    'label' => 'Maintenance',
                    'icon' => 'heroicon-o-wrench-screwdriver',
                    'collapsed' => false,
                ],
                'Assets' => [
                    'label' => 'Asset Management', 
                    'icon' => 'heroicon-o-building-office',
                    'collapsed' => false,
                ],
                'Quality' => [
                    'label' => 'Quality & Inspection',
                    'icon' => 'heroicon-o-shield-check',
                    'collapsed' => false,
                ],
                'CRM' => [
                    'label' => 'Customer Relations',
                    'icon' => 'heroicon-o-users',
                    'collapsed' => true,
                ],
                'Reports' => [
                    'label' => 'Reports & Analytics',
                    'icon' => 'heroicon-o-chart-bar',
                    'collapsed' => true,
                ],
                'Settings' => [
                    'label' => 'System Settings',
                    'icon' => 'heroicon-o-cog-6-tooth',
                    'collapsed' => true,
                ],
            ])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->userMenuItems([
                MenuItem::make()
                    ->label('Profile')
                    ->icon('heroicon-o-user-circle')
                    ->url(fn () => $this->shouldRegisterMenuItem()
                        ? url(EditProfile::getUrl())
                        : url($panel->getPath())),
            ])
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\\Filament\\App\\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\\Filament\\App\\Pages')
            ->pages([
                Dashboard::class,
                Pages\EditProfile::class,
            ])
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\\Filament\\App\\Widgets')
            ->widgets([
                \App\Filament\App\Widgets\MaintenanceStatsWidget::class,
                \App\Filament\App\Widgets\EquipmentStatusWidget::class,
                \App\Filament\App\Widgets\RecentWorkOrdersWidget::class,
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                TeamsPermission::class,
            ])
            ->plugins([
                // \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()
            ]);

        // if (Features::hasApiFeatures()) {
        //     $panel->userMenuItems([
        //         MenuItem::make()
        //             ->label('API Tokens')
        //             ->icon('heroicon-o-key')
        //             ->url(fn () => $this->shouldRegisterMenuItem()
        //                 ? url(Pages\ApiTokenManagerPage::getUrl())
        //                 : url($panel->getPath())),
        //     ]);
        // }

        if (Features::hasTeamFeatures()) {
            $panel
                ->tenant(Team::class, ownershipRelationship: 'team')
                ->tenantRegistration(Pages\CreateTeam::class)
                ->tenantProfile(Pages\EditTeam::class)
                ->userMenuItems([
                    MenuItem::make()
                        ->label('Team Settings')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->url(fn () => $this->shouldRegisterMenuItem()
                            ? url(Pages\EditTeam::getUrl())
                            : url($panel->getPath())),
                ]);
        }

        return $panel;
    }

    public function boot()
    {
        /**
         * Disable Fortify routes.
         */
        Fortify::$registersRoutes = false;

        /**
         * Disable Jetstream routes.
         */
        Jetstream::$registersRoutes = false;

        /**
         * Listen and create personal team for new accounts.
         */
        Event::listen(
            Registered::class,
            CreatePersonalTeam::class,
        );

        /**
         * Listen and switch team if tenant was changed.
         */
        Event::listen(
            TenantSet::class,
            SwitchTeam::class,
        );
    }

    public function shouldRegisterMenuItem(): bool
    {
        return true; //auth()->user()?->hasVerifiedEmail() && Filament::hasTenancy() && Filament::getTenant();
    }
}
