<?php

namespace App\Filament\App\Widgets;

use App\Services\MaintenanceReportService;
use Filament\Widgets\ChartWidget;

class MaintenanceCostAnalysisWidget extends ChartWidget
{
    protected ?string $heading = 'Maintenance Cost Analysis (Last 30 Days)';

    protected ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 1;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $teamId = filament()->getTenant()?->id;
        $reportService = app(MaintenanceReportService::class);
        
        $costAnalysis = $reportService->generateCostAnalysis($teamId);

        return [
            'datasets' => [
                [
                    'label' => 'Cost Breakdown',
                    'data' => [
                        $costAnalysis['parts_cost'],
                        $costAnalysis['labor_cost'],
                    ],
                    'backgroundColor' => [
                        '#3b82f6',
                        '#10b981',
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => ['Parts Cost', 'Labor Cost'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        $teamId = filament()->getTenant()?->id;
        $reportService = app(MaintenanceReportService::class);
        $costAnalysis = $reportService->generateCostAnalysis($teamId);

        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 15,
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            let label = context.label || "";
                            if (label) {
                                label += ": ";
                            }
                            label += "$" + context.parsed.toLocaleString();
                            return label;
                        }',
                    ],
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Total: $' . number_format($costAnalysis['total_cost'], 2),
                    'position' => 'top',
                    'font' => [
                        'size' => 14,
                        'weight' => 'bold',
                    ],
                ],
            ],
        ];
    }
}
