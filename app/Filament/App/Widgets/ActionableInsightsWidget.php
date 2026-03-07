<?php

namespace App\Filament\App\Widgets;

use App\Services\MaintenanceReportService;
use Filament\Widgets\Widget;

class ActionableInsightsWidget extends Widget
{
    protected ?string $heading = 'Actionable Insights & Recommendations';

    protected  int | string | array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    protected string $view = 'filament.widgets.actionable-insights';

    protected function getViewData(): array
    {
        $teamId = filament()->getTenant()?->id;
        $reportService = app(MaintenanceReportService::class);
        
        $report = $reportService->generateComprehensiveReport($teamId);

        return [
            'insights' => $report['actionable_insights'],
            'mttr' => $report['mttr'],
            'costAnalysis' => $report['cost_analysis'],
        ];
    }
}
