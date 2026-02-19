<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MaintenanceReportService
{
    /**
     * Calculate Mean Time To Repair (MTTR) for work orders
     * 
     * @param int|null $teamId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float Average hours to complete work orders
     */
    public function calculateMTTR(?int $teamId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        $query = WorkOrder::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->whereNotNull('completed_at')
            ->whereNotNull('started_at');

        if ($startDate) {
            $query->where('completed_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('completed_at', '<=', $endDate);
        }

        $workOrders = $query->get();

        if ($workOrders->isEmpty()) {
            return 0;
        }

        $totalHours = $workOrders->sum(function ($workOrder) {
            return $workOrder->started_at->diffInHours($workOrder->completed_at);
        });

        return round($totalHours / $workOrders->count(), 2);
    }

    /**
     * Calculate equipment uptime percentage
     * 
     * @param int $equipmentId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float Uptime percentage (0-100)
     */
    public function calculateEquipmentUptime(int $equipmentId, ?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $totalDays = $startDate->diffInDays($endDate);
        
        if ($totalDays === 0) {
            return 100.0;
        }

        // Calculate days under maintenance
        $maintenanceDays = WorkOrder::where('equipment_id', $equipmentId)
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('started_at', [$startDate, $endDate])
                    ->orWhereBetween('completed_at', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('started_at', '<=', $startDate)
                          ->where('completed_at', '>=', $endDate);
                    });
            })
            ->get()
            ->sum(function ($workOrder) use ($startDate, $endDate) {
                $start = $workOrder->started_at->max($startDate);
                $end = $workOrder->completed_at->min($endDate);
                return $start->diffInDays($end);
            });

        $uptime = (($totalDays - $maintenanceDays) / $totalDays) * 100;
        
        return round(max(0, min(100, $uptime)), 2);
    }

    /**
     * Generate cost analysis for work orders
     * 
     * @param int|null $teamId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array Cost breakdown
     */
    public function generateCostAnalysis(?int $teamId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = WorkOrder::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->whereNotNull('completed_at');

        if ($startDate) {
            $query->where('completed_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('completed_at', '<=', $endDate);
        }

        $workOrders = $query->with('inventoryParts')->get();

        $partsCost = 0;
        $laborCost = 0;
        $totalWorkOrders = $workOrders->count();

        foreach ($workOrders as $workOrder) {
            // Calculate parts cost
            $partsCost += $workOrder->inventoryParts->sum(function ($part) {
                return ($part->pivot->quantity_used ?? 0) * ($part->pivot->unit_cost ?? 0);
            });

            // Calculate labor cost (assuming $50/hour average)
            $laborCost += ($workOrder->actual_hours ?? 0) * 50;
        }

        $totalCost = $partsCost + $laborCost;

        return [
            'parts_cost' => round($partsCost, 2),
            'labor_cost' => round($laborCost, 2),
            'total_cost' => round($totalCost, 2),
            'average_cost_per_work_order' => $totalWorkOrders > 0 ? round($totalCost / $totalWorkOrders, 2) : 0,
            'total_work_orders' => $totalWorkOrders,
        ];
    }

    /**
     * Get equipment performance metrics
     * 
     * @param int|null $teamId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array Performance metrics for each equipment
     */
    public function getEquipmentPerformanceMetrics(?int $teamId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $equipment = Equipment::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->with(['workOrders' => function ($query) use ($startDate, $endDate) {
                $query->whereNotNull('completed_at')
                    ->where('completed_at', '>=', $startDate)
                    ->where('completed_at', '<=', $endDate);
            }])
            ->get();

        $metrics = [];

        foreach ($equipment as $item) {
            $workOrderCount = $item->workOrders->count();
            $totalCost = $item->workOrders->sum(function ($wo) {
                $partsCost = $wo->inventoryParts->sum(function ($part) {
                    return ($part->pivot->quantity_used ?? 0) * ($part->pivot->unit_cost ?? 0);
                });
                $laborCost = ($wo->actual_hours ?? 0) * 50;
                return $partsCost + $laborCost;
            });

            $metrics[] = [
                'equipment_id' => $item->id,
                'equipment_name' => $item->name,
                'serial_number' => $item->serial_number,
                'criticality' => $item->criticality,
                'work_order_count' => $workOrderCount,
                'total_cost' => round($totalCost, 2),
                'average_cost' => $workOrderCount > 0 ? round($totalCost / $workOrderCount, 2) : 0,
                'uptime_percentage' => $this->calculateEquipmentUptime($item->id, $startDate, $endDate),
                'failure_rate' => $workOrderCount, // Number of failures/maintenance in period
            ];
        }

        // Sort by total cost descending
        usort($metrics, fn($a, $b) => $b['total_cost'] <=> $a['total_cost']);

        return $metrics;
    }

    /**
     * Get technician performance metrics
     * 
     * @param int|null $teamId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array Performance metrics for each technician
     */
    public function getTechnicianPerformanceMetrics(?int $teamId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = WorkOrder::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->whereNotNull('assigned_to');

        if ($startDate) {
            $query->where('submitted_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('submitted_at', '<=', $endDate);
        }

        $workOrders = $query->with('assignedTo')->get();

        $technicianMetrics = [];

        foreach ($workOrders->groupBy('assigned_to') as $userId => $userWorkOrders) {
            $technician = $userWorkOrders->first()->assignedTo;
            if (!$technician) {
                continue;
            }

            $completed = $userWorkOrders->where('status', 'completed')->count();
            $total = $userWorkOrders->count();
            $completionRate = $total > 0 ? ($completed / $total) * 100 : 0;

            $avgCompletionTime = $userWorkOrders
                ->filter(fn($wo) => $wo->completed_at && $wo->started_at)
                ->avg(fn($wo) => $wo->started_at->diffInHours($wo->completed_at));

            $technicianMetrics[] = [
                'technician_id' => $userId,
                'technician_name' => $technician->name,
                'total_assigned' => $total,
                'completed' => $completed,
                'in_progress' => $userWorkOrders->where('status', 'in_progress')->count(),
                'pending' => $userWorkOrders->where('status', 'pending')->count(),
                'completion_rate' => round($completionRate, 2),
                'average_completion_time_hours' => round($avgCompletionTime ?? 0, 2),
            ];
        }

        // Sort by completion rate descending
        usort($technicianMetrics, fn($a, $b) => $b['completion_rate'] <=> $a['completion_rate']);

        return $technicianMetrics;
    }

    /**
     * Analyze maintenance trends
     * 
     * @param int|null $teamId
     * @param int $days
     * @return array Trend data
     */
    public function analyzeMaintenanceTrends(?int $teamId = null, int $days = 90): array
    {
        $startDate = now()->subDays($days);

        $workOrders = WorkOrder::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->where('submitted_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(submitted_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN priority = "urgent" THEN 1 ELSE 0 END) as urgent'),
                DB::raw('SUM(CASE WHEN priority = "high" THEN 1 ELSE 0 END) as high')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Calculate week-over-week change
        $thisWeek = WorkOrder::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->where('submitted_at', '>=', now()->subDays(7))
            ->count();

        $lastWeek = WorkOrder::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->whereBetween('submitted_at', [now()->subDays(14), now()->subDays(7)])
            ->count();

        $weekOverWeekChange = $lastWeek > 0 ? (($thisWeek - $lastWeek) / $lastWeek) * 100 : 0;

        return [
            'daily_data' => $workOrders->toArray(),
            'week_over_week_change' => round($weekOverWeekChange, 2),
            'this_week_total' => $thisWeek,
            'last_week_total' => $lastWeek,
            'average_daily_work_orders' => round($workOrders->avg('total') ?? 0, 2),
            'peak_day' => $workOrders->sortByDesc('total')->first(),
        ];
    }

    /**
     * Generate comprehensive maintenance report
     * 
     * @param int|null $teamId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array Complete report data
     */
    public function generateComprehensiveReport(?int $teamId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        return [
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'days' => $startDate->diffInDays($endDate),
            ],
            'mttr' => $this->calculateMTTR($teamId, $startDate, $endDate),
            'cost_analysis' => $this->generateCostAnalysis($teamId, $startDate, $endDate),
            'equipment_performance' => $this->getEquipmentPerformanceMetrics($teamId, $startDate, $endDate),
            'technician_performance' => $this->getTechnicianPerformanceMetrics($teamId, $startDate, $endDate),
            'trends' => $this->analyzeMaintenanceTrends($teamId, $startDate->diffInDays($endDate)),
            'actionable_insights' => $this->generateActionableInsights($teamId, $startDate, $endDate),
        ];
    }

    /**
     * Generate actionable insights based on data analysis
     * 
     * @param int|null $teamId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array List of insights
     */
    protected function generateActionableInsights(?int $teamId, Carbon $startDate, Carbon $endDate): array
    {
        $insights = [];

        // Check for high-cost equipment
        $equipmentMetrics = $this->getEquipmentPerformanceMetrics($teamId, $startDate, $endDate);
        $highCostEquipment = array_filter($equipmentMetrics, fn($e) => $e['total_cost'] > 5000);
        
        if (!empty($highCostEquipment)) {
            $insights[] = [
                'type' => 'warning',
                'category' => 'Cost Management',
                'message' => count($highCostEquipment) . ' equipment items have exceeded $5,000 in maintenance costs.',
                'recommendation' => 'Consider evaluating replacement vs. repair costs for high-maintenance equipment.',
            ];
        }

        // Check for low uptime equipment
        $lowUptimeEquipment = array_filter($equipmentMetrics, fn($e) => $e['uptime_percentage'] < 80);
        
        if (!empty($lowUptimeEquipment)) {
            $insights[] = [
                'type' => 'critical',
                'category' => 'Equipment Reliability',
                'message' => count($lowUptimeEquipment) . ' equipment items have uptime below 80%.',
                'recommendation' => 'Implement preventive maintenance schedules to improve equipment availability.',
            ];
        }

        // Check MTTR
        $mttr = $this->calculateMTTR($teamId, $startDate, $endDate);
        if ($mttr > 24) {
            $insights[] = [
                'type' => 'warning',
                'category' => 'Response Time',
                'message' => "Average repair time is {$mttr} hours, which exceeds the 24-hour target.",
                'recommendation' => 'Review staffing levels and parts inventory to reduce response times.',
            ];
        }

        // Check for overdue schedules
        $overdueCount = MaintenanceSchedule::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->where('next_due_date', '<', now())
            ->where('status', 'active')
            ->count();

        if ($overdueCount > 0) {
            $insights[] = [
                'type' => 'critical',
                'category' => 'Preventive Maintenance',
                'message' => "{$overdueCount} maintenance schedules are overdue.",
                'recommendation' => 'Prioritize preventive maintenance to avoid unexpected equipment failures.',
            ];
        }

        // Check technician workload
        $techMetrics = $this->getTechnicianPerformanceMetrics($teamId, $startDate, $endDate);
        $overloadedTechs = array_filter($techMetrics, fn($t) => $t['completion_rate'] < 70 && $t['total_assigned'] > 5);
        
        if (!empty($overloadedTechs)) {
            $insights[] = [
                'type' => 'info',
                'category' => 'Resource Management',
                'message' => count($overloadedTechs) . ' technicians have completion rates below 70%.',
                'recommendation' => 'Review workload distribution and consider additional training or resources.',
            ];
        }

        return $insights;
    }
}
