<?php

namespace App\Filament\App\Widgets;

use App\Models\Equipment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EquipmentHealthWidget extends ChartWidget
{
    protected ?string $heading = 'Equipment Health Score';

    protected static ?string $pollingInterval = '60s';

    protected int | string | array $columnSpan = 1;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $teamId = filament()->getTenant()?->id;

        // Simulate health scores based on equipment age and maintenance history
        $healthData = Equipment::query()
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->select('name', 'purchase_date', 'status')
            ->get()
            ->map(function ($equipment) {
                $ageInYears = $equipment->purchase_date ? 
                    now()->diffInYears($equipment->purchase_date) : 0;

                // Calculate health score (100 = excellent, 0 = critical)
                $baseScore = 100;
                $ageDeduction = min($ageInYears * 10, 50); // Max 50 points for age
                $statusDeduction = match($equipment->status) {
                    'active' => 0,
                    'inactive' => 20,
                    'under_maintenance' => 30,
                    'retired' => 80,
                    default => 10,
                };

                $healthScore = max(0, $baseScore - $ageDeduction - $statusDeduction);

                return [
                    'name' => $equipment->name,
                    'health' => $healthScore,
                    'category' => $this->getHealthCategory($healthScore),
                ];
            });

        // Group by health categories
        $categories = $healthData->groupBy('category');

        $labels = [];
        $data = [];
        $colors = [];

        $categoryConfig = [
            'Excellent' => ['color' => '#10b981', 'range' => '90-100%'],
            'Good' => ['color' => '#84cc16', 'range' => '70-89%'],
            'Fair' => ['color' => '#f59e0b', 'range' => '50-69%'],
            'Poor' => ['color' => '#ef4444', 'range' => '0-49%'],
        ];

        foreach ($categoryConfig as $category => $config) {
            $count = $categories->get($category, collect())->count();
            if ($count > 0) {
                $labels[] = $category;
                $data[] = $count;
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

    private function getHealthCategory(int $score): string
    {
        if ($score >= 90) return 'Excellent';
        if ($score >= 70) return 'Good';
        if ($score >= 50) return 'Fair';
        return 'Poor';
    }
}