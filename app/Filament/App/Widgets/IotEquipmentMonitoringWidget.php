<?php

namespace App\Filament\App\Widgets;

use App\Models\Equipment;
use App\Services\IotSensorService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class IotEquipmentMonitoringWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';

    public function getStats(): array
    {
        $sensorService = app(IotSensorService::class);
        $dashboardData = $sensorService->getRealTimeDashboardData();

        return [
            Stat::make('Total Monitored Equipment', $dashboardData['total_monitored'])
                ->description('Equipment with IoT sensors enabled')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('primary'),

            Stat::make('Healthy Equipment', $dashboardData['healthy'])
                ->description('Operating within normal parameters')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Warning Status', $dashboardData['warning'])
                ->description('Equipment requiring attention')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('Critical Status', $dashboardData['critical'])
                ->description('Equipment requiring immediate action')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),
        ];
    }

    protected function getPollingInterval(): ?string
    {
        return '30s'; // Refresh every 30 seconds for real-time monitoring
    }
}
