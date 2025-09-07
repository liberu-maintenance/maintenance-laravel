<?php

namespace App\Filament\App\Widgets;

use App\Models\WorkOrder;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use Filament\Widgets\Widget;

class QuickStatsWidget extends Widget
{
    protected string $view = 'filament.app.widgets.quick-stats';

    protected int | string | array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    public function getViewData(): array
    {
        $teamId = filament()->getTenant()?->id;

        // Critical metrics that need immediate attention
        $criticalMetrics = [
            'urgent_work_orders' => WorkOrder::query()
                ->when($teamId, fn($q) => $q->where('team_id', $teamId))
                ->where('priority', 'urgent')
                ->whereIn('status', ['pending', 'approved', 'in_progress'])
                ->count(),

            'overdue_maintenance' => MaintenanceSchedule::query()
                ->when($teamId, fn($q) => $q->where('team_id', $teamId))
                ->where('status', 'active')
                ->where('next_due_date', '<', now())
                ->count(),

            'equipment_down' => Equipment::query()
                ->when($teamId, fn($q) => $q->where('team_id', $teamId))
                ->whereIn('status', ['under_maintenance', 'retired'])
                ->count(),

            'pending_approvals' => WorkOrder::query()
                ->when($teamId, fn($q) => $q->where('team_id', $teamId))
                ->where('status', 'pending')
                ->count(),
        ];

        // Performance metrics
        $performanceMetrics = [
            'completion_rate' => $this->getCompletionRate($teamId),
            'avg_response_time' => $this->getAverageResponseTime($teamId),
            'equipment_uptime' => $this->getEquipmentUptime($teamId),
            'maintenance_compliance' => $this->getMaintenanceCompliance($teamId),
        ];

        return [
            'critical' => $criticalMetrics,
            'performance' => $performanceMetrics,
            'alerts' => $this->getAlerts($criticalMetrics),
        ];
    }

    private function getCompletionRate(?int $teamId): float
    {
        $total = WorkOrder::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->where('submitted_at', '>=', now()->subDays(30))
            ->count();

        if ($total === 0) return 100.0;

        $completed = WorkOrder::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->where('submitted_at', '>=', now()->subDays(30))
            ->where('status', 'completed')
            ->count();

        return round(($completed / $total) * 100, 1);
    }

    private function getAverageResponseTime(?int $teamId): float
    {
        $workOrders = WorkOrder::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->whereNotNull('approved_at')
            ->where('submitted_at', '>=', now()->subDays(30))
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, submitted_at, approved_at)) as avg_hours')
            ->first();

        return round($workOrders->avg_hours ?? 0, 1);
    }

    private function getEquipmentUptime(?int $teamId): float
    {
        $total = Equipment::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->count();

        if ($total === 0) return 100.0;

        $operational = Equipment::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->where('status', 'active')
            ->count();

        return round(($operational / $total) * 100, 1);
    }

    private function getMaintenanceCompliance(?int $teamId): float
    {
        $total = MaintenanceSchedule::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->where('status', 'active')
            ->count();

        if ($total === 0) return 100.0;

        $compliant = MaintenanceSchedule::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->where('status', 'active')
            ->where('next_due_date', '>=', now())
            ->count();

        return round(($compliant / $total) * 100, 1);
    }

    private function getAlerts(array $metrics): array
    {
        $alerts = [];

        if ($metrics['urgent_work_orders'] > 0) {
            $alerts[] = [
                'type' => 'critical',
                'message' => "{$metrics['urgent_work_orders']} urgent work orders require immediate attention",
                'action' => 'View Work Orders',
                'url' => route('filament.app.resources.work-orders.work-orders.index', ['tableFilters[priority][value]' => 'urgent']),
            ];
        }

        if ($metrics['overdue_maintenance'] > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$metrics['overdue_maintenance']} maintenance schedules are overdue",
                'action' => 'View Schedules',
                'url' => route('filament.app.resources.maintenance-schedules.maintenance-schedules.index'),
            ];
        }

        if ($metrics['equipment_down'] > 3) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$metrics['equipment_down']} pieces of equipment are not operational",
                'action' => 'View Equipment',
                'url' => route('filament.app.resources.equipment.equipment.index'),
            ];
        }

        return $alerts;
    }
}