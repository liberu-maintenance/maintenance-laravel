<?php

namespace App\Filament\App\Widgets;

use App\Services\MaintenanceReportService;
use Filament\Widgets\ChartWidget;

class AdvancedMaintenanceTrendsWidget extends ChartWidget
{
    protected ?string $heading = 'Maintenance Trends Analysis (Last 90 Days)';

    protected ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '400px';

    protected function getData(): array
    {
        $teamId = filament()->getTenant()?->id;
        $reportService = app(MaintenanceReportService::class);
        
        $trends = $reportService->analyzeMaintenanceTrends($teamId, 90);
        $dailyData = collect($trends['daily_data']);

        $labels = $dailyData->pluck('date')->map(function ($date) {
            return \Carbon\Carbon::parse($date)->format('M j');
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Work Orders',
                    'data' => $dailyData->pluck('total')->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Completed',
                    'data' => $dailyData->pluck('completed')->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Urgent Priority',
                    'data' => $dailyData->pluck('urgent')->toArray(),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'High Priority',
                    'data' => $dailyData->pluck('high')->toArray(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
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
                    'position' => 'left',
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Number of Work Orders',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'maxRotation' => 45,
                        'minRotation' => 45,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 15,
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

    protected function getFooter(): ?string
    {
        $teamId = filament()->getTenant()?->id;
        $reportService = app(MaintenanceReportService::class);
        $trends = $reportService->analyzeMaintenanceTrends($teamId, 90);

        $change = $trends['week_over_week_change'];
        $changeText = $change > 0 ? "↑ {$change}%" : ($change < 0 ? "↓ " . abs($change) . "%" : "No change");
        $changeColor = $change > 10 ? 'text-danger-600' : ($change < -10 ? 'text-success-600' : 'text-gray-600');

        return view('filament.widgets.trends-footer', [
            'changeText' => $changeText,
            'changeColor' => $changeColor,
            'avgDaily' => number_format($trends['average_daily_work_orders'], 2),
        ])->render();
    }
}
