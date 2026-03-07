<?php

namespace App\Filament\App\Widgets;

use App\Models\WorkOrder;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class MaintenanceStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Get current team context
        $teamId = filament()->getTenant()?->id;

        // Work Orders Statistics
        $totalWorkOrders = WorkOrder::when($teamId, fn($q) => $q->where('team_id', $teamId))->count();
        $pendingWorkOrders = WorkOrder::when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->where('status', 'pending')->count();
        $urgentWorkOrders = WorkOrder::when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->where('priority', 'urgent')->count();

        // Equipment Statistics
        $totalEquipment = Equipment::when($teamId, fn($q) => $q->where('team_id', $teamId))->count();
        $criticalEquipment = Equipment::when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->where('criticality', 'critical')->count();
        $underMaintenance = Equipment::when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->where('status', 'under_maintenance')->count();

        // Maintenance Schedule Statistics
        $overdueSchedules = MaintenanceSchedule::when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->where('next_due_date', '<', now())
            ->where('status', 'active')
            ->count();

        $dueSoonSchedules = MaintenanceSchedule::when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->whereBetween('next_due_date', [now(), now()->addDays(7)])
            ->where('status', 'active')
            ->count();

        return [
            Stat::make('Total Work Orders', $totalWorkOrders)
                ->description($pendingWorkOrders . ' pending')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingWorkOrders > 5 ? 'warning' : 'success')
                ->chart([7, 12, 8, 15, 10, 18, $totalWorkOrders]),

            Stat::make('Urgent Work Orders', $urgentWorkOrders)
                ->description($urgentWorkOrders > 0 ? 'Immediate attention required' : 'No urgent items')
                ->descriptionIcon($urgentWorkOrders > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($urgentWorkOrders > 0 ? 'danger' : 'success')
                ->extraAttributes([
                    'class' => $urgentWorkOrders > 0 ? 'priority-critical' : '',
                ]),

            Stat::make('Equipment Status', $totalEquipment)
                ->description($underMaintenance . ' under maintenance, ' . $criticalEquipment . ' critical')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color($underMaintenance > 3 ? 'warning' : 'info')
                ->chart([5, 8, 6, 10, 7, 12, $totalEquipment]),

            Stat::make('Overdue Schedules', $overdueSchedules)
                ->description($dueSoonSchedules . ' due within 7 days')
                ->descriptionIcon($overdueSchedules > 0 ? 'heroicon-m-calendar-days' : 'heroicon-m-check-badge')
                ->color($overdueSchedules > 0 ? 'danger' : ($dueSoonSchedules > 0 ? 'warning' : 'success'))
                ->extraAttributes([
                    'class' => $overdueSchedules > 0 ? 'schedule-overdue' : ($dueSoonSchedules > 0 ? 'schedule-due-soon' : 'schedule-completed'),
                ]),
        ];
    }
}