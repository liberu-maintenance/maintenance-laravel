<?php

namespace App\Filament\App\Widgets;

use App\Models\WorkOrder;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class WorkOrderTrendsWidget extends ChartWidget
{
    protected ?string $heading = 'Work Order Trends (Last 30 Days)';

    protected static ?string $pollingInterval = '300s';

    protected int | string | array $columnSpan = 1;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $teamId = filament()->getTenant()?->id;

        // Get work orders for the last 30 days
        $workOrders = WorkOrder::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->where('submitted_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(submitted_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN priority = "urgent" THEN 1 ELSE 0 END) as urgent')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill in missing dates with zero values
        $dates = collect();
        for ($i = 29; $i >= 0; $i--) {
            $dates->push(now()->subDays($i)->format('Y-m-d'));
        }

        $labels = [];
        $totalData = [];
        $completedData = [];
        $urgentData = [];

        foreach ($dates as $date) {
            $dayData = $workOrders->firstWhere('date', $date);

            $labels[] = now()->createFromFormat('Y-m-d', $date)->format('M j');
            $totalData[] = $dayData ? $dayData->total : 0;
            $completedData[] = $dayData ? $dayData->completed : 0;
            $urgentData[] = $dayData ? $dayData->urgent : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Work Orders',
                    'data' => $totalData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Completed',
                    'data' => $completedData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Urgent',
                    'data' => $urgentData,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                    ],
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
        ];
    }
}