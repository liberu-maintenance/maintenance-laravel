<?php

namespace App\Filament\App\Widgets;

use App\Services\MaintenanceReportService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TechnicianPerformanceWidget extends BaseWidget
{
    protected static ?string $heading = 'Technician Performance Report';

    protected static int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        $teamId = filament()->getTenant()?->id;
        $reportService = app(MaintenanceReportService::class);
        
        $metrics = $reportService->getTechnicianPerformanceMetrics($teamId);

        return $table
            ->query(
                \App\Models\User::query()->whereIn('id', collect($metrics)->pluck('technician_id'))
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Technician')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('total_assigned')
                    ->label('Assigned')
                    ->state(function ($record) use ($metrics) {
                        $metric = collect($metrics)->firstWhere('technician_id', $record->id);
                        return $metric['total_assigned'] ?? 0;
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('completed')
                    ->label('Completed')
                    ->state(function ($record) use ($metrics) {
                        $metric = collect($metrics)->firstWhere('technician_id', $record->id);
                        return $metric['completed'] ?? 0;
                    })
                    ->badge()
                    ->color('success')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('in_progress')
                    ->label('In Progress')
                    ->state(function ($record) use ($metrics) {
                        $metric = collect($metrics)->firstWhere('technician_id', $record->id);
                        return $metric['in_progress'] ?? 0;
                    })
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('pending')
                    ->label('Pending')
                    ->state(function ($record) use ($metrics) {
                        $metric = collect($metrics)->firstWhere('technician_id', $record->id);
                        return $metric['pending'] ?? 0;
                    })
                    ->badge()
                    ->color('info')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('completion_rate')
                    ->label('Completion Rate')
                    ->state(function ($record) use ($metrics) {
                        $metric = collect($metrics)->firstWhere('technician_id', $record->id);
                        return $metric['completion_rate'] ?? 0;
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 60 ? 'warning' : 'danger'))
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('average_completion_time')
                    ->label('Avg Time (hrs)')
                    ->state(function ($record) use ($metrics) {
                        $metric = collect($metrics)->firstWhere('technician_id', $record->id);
                        return $metric['average_completion_time_hours'] ?? 0;
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->sortable(),
            ])
            ->defaultSort('completion_rate', 'desc')
            ->paginated([10, 25, 50]);
    }
}
