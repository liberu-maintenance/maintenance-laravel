<?php

namespace App\Filament\App\Pages;

use App\Services\MaintenanceReportService;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class MaintenanceReports extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.app.pages.maintenance-reports';

    protected static string | \UnitEnum | null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public ?array $reportData = null;

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => now()->subDays(30)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Report Parameters')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->default(now()->subDays(30)),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->required()
                            ->default(now()),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function generateReport(): void
    {
        $data = $this->form->getState();
        
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        if ($endDate->lt($startDate)) {
            Notification::make()
                ->title('Invalid Date Range')
                ->body('End date must be after start date.')
                ->danger()
                ->send();
            return;
        }

        $teamId = filament()->getTenant()?->id;
        $reportService = app(MaintenanceReportService::class);

        $this->reportData = $reportService->generateComprehensiveReport($teamId, $startDate, $endDate);

        Notification::make()
            ->title('Report Generated')
            ->body('The maintenance report has been generated successfully.')
            ->success()
            ->send();
    }

    public function exportPdf(): \Illuminate\Http\Response
    {
        if (!$this->reportData) {
            Notification::make()
                ->title('No Report Data')
                ->body('Please generate a report first.')
                ->warning()
                ->send();
            return Response::make('', 400);
        }

        $pdf = Pdf::loadView('reports.maintenance-comprehensive', [
            'report' => $this->reportData,
        ]);

        return Response::make($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="maintenance-report-' . now()->format('Y-m-d') . '.pdf"',
        ]);
    }

    public function exportCsv(): \Illuminate\Http\Response
    {
        if (!$this->reportData) {
            Notification::make()
                ->title('No Report Data')
                ->body('Please generate a report first.')
                ->warning()
                ->send();
            return Response::make('', 400);
        }

        $csv = $this->generateCsvContent($this->reportData);

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="maintenance-report-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    protected function generateCsvContent(array $report): string
    {
        $output = fopen('php://temp', 'r+');

        // Header
        fputcsv($output, ['Maintenance Report']);
        fputcsv($output, ['Period', $report['period']['start_date'] . ' to ' . $report['period']['end_date']]);
        fputcsv($output, []);

        // Summary Metrics
        fputcsv($output, ['Summary Metrics']);
        fputcsv($output, ['MTTR (hours)', $report['mttr']]);
        fputcsv($output, ['Total Cost', '$' . $report['cost_analysis']['total_cost']]);
        fputcsv($output, ['Parts Cost', '$' . $report['cost_analysis']['parts_cost']]);
        fputcsv($output, ['Labor Cost', '$' . $report['cost_analysis']['labor_cost']]);
        fputcsv($output, ['Total Work Orders', $report['cost_analysis']['total_work_orders']]);
        fputcsv($output, []);

        // Equipment Performance
        fputcsv($output, ['Equipment Performance']);
        fputcsv($output, ['Equipment', 'Serial Number', 'Work Orders', 'Uptime %', 'Total Cost', 'Avg Cost']);
        foreach ($report['equipment_performance'] as $equipment) {
            fputcsv($output, [
                $equipment['equipment_name'],
                $equipment['serial_number'],
                $equipment['work_order_count'],
                $equipment['uptime_percentage'],
                '$' . $equipment['total_cost'],
                '$' . $equipment['average_cost'],
            ]);
        }
        fputcsv($output, []);

        // Technician Performance
        fputcsv($output, ['Technician Performance']);
        fputcsv($output, ['Technician', 'Assigned', 'Completed', 'In Progress', 'Completion Rate %', 'Avg Time (hrs)']);
        foreach ($report['technician_performance'] as $tech) {
            fputcsv($output, [
                $tech['technician_name'],
                $tech['total_assigned'],
                $tech['completed'],
                $tech['in_progress'],
                $tech['completion_rate'],
                $tech['average_completion_time_hours'],
            ]);
        }
        fputcsv($output, []);

        // Actionable Insights
        fputcsv($output, ['Actionable Insights']);
        fputcsv($output, ['Type', 'Category', 'Message', 'Recommendation']);
        foreach ($report['actionable_insights'] as $insight) {
            fputcsv($output, [
                $insight['type'],
                $insight['category'],
                $insight['message'],
                $insight['recommendation'],
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
