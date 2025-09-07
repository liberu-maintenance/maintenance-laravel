<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;
use App\Filament\App\Widgets\MaintenanceStatsWidget;
use App\Filament\App\Widgets\EquipmentStatusWidget;
use App\Filament\App\Widgets\RecentWorkOrdersWidget;
use App\Filament\App\Widgets\MaintenanceCalendarWidget;
use App\Filament\App\Widgets\EquipmentHealthWidget;
use App\Filament\App\Widgets\WorkOrderTrendsWidget;

class Dashboard extends BaseDashboard
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';

    protected string $view = 'filament.app.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            AccountWidget::class,
            MaintenanceStatsWidget::class,
            EquipmentStatusWidget::class,
            EquipmentHealthWidget::class,
            WorkOrderTrendsWidget::class,
            MaintenanceCalendarWidget::class,
            RecentWorkOrdersWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
            'xl' => 4,
        ];
    }

    public function getTitle(): string
    {
        return 'Maintenance Dashboard';
    }

    public function getHeading(): string
    {
        $user = auth()->user();
        $greeting = $this->getGreeting();

        return "{$greeting}, {$user->name}!";
    }

    public function getSubheading(): ?string
    {
        return 'Welcome to your maintenance management dashboard. Here\'s an overview of your current operations.';
    }

    private function getGreeting(): string
    {
        $hour = now()->hour;

        if ($hour < 12) {
            return 'Good morning';
        } elseif ($hour < 17) {
            return 'Good afternoon';
        } else {
            return 'Good evening';
        }
    }
}