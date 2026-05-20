<?php

namespace App\Filament\App\Widgets;

use App\Models\Equipment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EquipmentStatusWidget extends ChartWidget
{
    protected ?string $heading = 'Equipment Status Overview';

    protected ?string $pollingInterval = '60s';

    protected int | string | array $columnSpan = 1;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $teamId = filament()->getTenant()?->id;

        $statusCounts = Equipment::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $labels = [];
        $data = [];
        $colors = [];

        $statusConfig = [
            'active' => ['label' => 'Active', 'color' => '#10b981'],
            'inactive' => ['label' => 'Inactive', 'color' => '#6b7280'],
            'under_maintenance' => ['label' => 'Under Maintenance', 'color' => '#f59e0b'],
            'retired' => ['label' => 'Retired', 'color' => '#ef4444'],
        ];

        foreach ($statusConfig as $status => $config) {
            if (isset($statusCounts[$status]) && $statusCounts[$status] > 0) {
                $labels[] = $config['label'];
                $data[] = $statusCounts[$status];
                $colors[] = $config['color'];
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Equipment Count',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.label + ": " + context.parsed + " equipment";
                        }',
                    ],
                ],
            ],
            'cutout' => '60%',
        ];
    }
}