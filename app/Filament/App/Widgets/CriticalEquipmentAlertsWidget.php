<?php

namespace App\Filament\App\Widgets;

use App\Models\Equipment;
use App\Services\IotSensorService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class CriticalEquipmentAlertsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Equipment::query()
                    ->sensorEnabled()
                    ->withCriticalReadings()
                    ->with(['recentSensorReadings', 'company'])
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Equipment')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->icon('heroicon-m-map-pin'),

                TextColumn::make('category')
                    ->label('Category')
                    ->badge(),

                BadgeColumn::make('status')
                    ->label('Operational Status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'under_maintenance',
                        'danger' => 'retired',
                        'secondary' => 'inactive',
                    ]),

                TextColumn::make('recentSensorReadings.metric_name')
                    ->label('Critical Metric')
                    ->limit(20),

                TextColumn::make('recentSensorReadings.value')
                    ->label('Value')
                    ->formatStateUsing(fn ($state, $record) => 
                        $state . ' ' . ($record->recentSensorReadings->first()?->unit ?? '')
                    ),

                BadgeColumn::make('recentSensorReadings.status')
                    ->label('Alert Status')
                    ->colors([
                        'danger' => 'critical',
                        'warning' => 'warning',
                    ]),

                TextColumn::make('last_sensor_reading_at')
                    ->label('Last Reading')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('last_sensor_reading_at', 'desc')
            ->poll('30s');
    }

    protected function getTableHeading(): ?string
    {
        return 'Critical Equipment Alerts - Requires Immediate Attention';
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'No Critical Alerts';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'All monitored equipment is operating within normal parameters.';
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-check-circle';
    }
}
