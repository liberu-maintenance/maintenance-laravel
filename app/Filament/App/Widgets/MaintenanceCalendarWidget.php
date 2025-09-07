<?php

namespace App\Filament\App\Widgets;

use App\Models\MaintenanceSchedule;
use App\Models\WorkOrder;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class MaintenanceCalendarWidget extends Widget
{
    protected string $view = 'filament.app.widgets.maintenance-calendar';

    protected ?string $heading = 'Upcoming Maintenance';

    protected int | string | array $columnSpan = 1;

    protected static ?string $pollingInterval = '300s';

    public function getViewData(): array
    {
        $teamId = filament()->getTenant()?->id;

        // Get upcoming maintenance schedules (next 14 days)
        $upcomingMaintenance = MaintenanceSchedule::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->with('equipment')
            ->where('status', 'active')
            ->where('next_due_date', '>=', now())
            ->where('next_due_date', '<=', now()->addDays(14))
            ->orderBy('next_due_date')
            ->get();

        // Get recent work orders that need attention
        $pendingWorkOrders = WorkOrder::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->whereIn('status', ['pending', 'approved', 'in_progress'])
            ->orderBy('priority', 'desc')
            ->orderBy('submitted_at', 'desc')
            ->limit(5)
            ->get();

        // Group maintenance by date
        $maintenanceByDate = $upcomingMaintenance->groupBy(function ($item) {
            return $item->next_due_date->format('Y-m-d');
        });

        // Create calendar data for next 14 days
        $calendarData = collect();
        for ($i = 0; $i < 14; $i++) {
            $date = now()->addDays($i);
            $dateKey = $date->format('Y-m-d');

            $calendarData->push([
                'date' => $date,
                'is_today' => $date->isToday(),
                'is_weekend' => $date->isWeekend(),
                'maintenance_items' => $maintenanceByDate->get($dateKey, collect()),
            ]);
        }

        return [
            'calendarData' => $calendarData,
            'pendingWorkOrders' => $pendingWorkOrders,
            'totalUpcoming' => $upcomingMaintenance->count(),
            'overdueCount' => MaintenanceSchedule::query()
                ->when($teamId, fn($q) => $q->where('team_id', $teamId))
                ->where('status', 'active')
                ->where('next_due_date', '<', now())
                ->count(),
        ];
    }
}