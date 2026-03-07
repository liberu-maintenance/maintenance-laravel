<?php

namespace App\Filament\App\Widgets;

use App\Services\MaintenanceReportService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class EquipmentPerformanceWidget extends BaseWidget
{
    protected static ?string $heading = 'Equipment Performance Report';

    protected static int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = null; // Disable polling for performance

    public function table(Table $table): Table
    {
        $teamId = filament()->getTenant()?->id;
        $reportService = app(MaintenanceReportService::class);
        
        $metrics = $reportService->getEquipmentPerformanceMetrics($teamId);

        return $table
            ->query(
                \App\Models\Equipment::query()->whereIn('id', collect($metrics)->pluck('equipment_id'))
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Equipment')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('serial_number')
                    ->label('Serial Number')
                    ->searchable(),
                    
                Tables\Columns\BadgeColumn::make('criticality')
                    ->label('Criticality')
                    ->colors([
                        'danger' => 'critical',
                        'warning' => 'high',
                        'success' => 'medium',
                        'secondary' => 'low',
                    ]),
                    
                Tables\Columns\TextColumn::make('work_order_count')
                    ->label('Work Orders')
                    ->state(function ($record) use ($metrics) {
                        $metric = collect($metrics)->firstWhere('equipment_id', $record->id);
                        return $metric['work_order_count'] ?? 0;
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('uptime')
                    ->label('Uptime %')
                    ->state(function ($record) use ($metrics) {
                        $metric = collect($metrics)->firstWhere('equipment_id', $record->id);
                        return $metric['uptime_percentage'] ?? 100;
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                    ->color(fn ($state) => $state >= 95 ? 'success' : ($state >= 80 ? 'warning' : 'danger'))
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Total Cost')
                    ->state(function ($record) use ($metrics) {
                        $metric = collect($metrics)->firstWhere('equipment_id', $record->id);
                        return $metric['total_cost'] ?? 0;
                    })
                    ->money('USD')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('average_cost')
                    ->label('Avg Cost/WO')
                    ->state(function ($record) use ($metrics) {
                        $metric = collect($metrics)->firstWhere('equipment_id', $record->id);
                        return $metric['average_cost'] ?? 0;
                    })
                    ->money('USD')
                    ->sortable(),
            ])
            ->defaultSort('total_cost', 'desc')
            ->paginated([10, 25, 50]);
    }
}
