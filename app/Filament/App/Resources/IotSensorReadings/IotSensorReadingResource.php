<?php

namespace App\Filament\App\Resources\IotSensorReadings;

use App\Models\IotSensorReading;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\App\Resources\IotSensorReadings\Pages\ListIotSensorReadings;

class IotSensorReadingResource extends Resource
{
    protected static ?string $model = IotSensorReading::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-signal';

    protected static string | \UnitEnum | null $navigationGroup = 'IoT Monitoring';

    protected static ?string $navigationLabel = 'Sensor Readings';

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('equipment.name')
                    ->label('Equipment')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sensor_type')
                    ->label('Sensor Type')
                    ->badge()
                    ->searchable(),

                TextColumn::make('metric_name')
                    ->label('Metric')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('value')
                    ->label('Value')
                    ->formatStateUsing(fn ($state, $record) => 
                        number_format($state, 2) . ' ' . ($record->unit ?? '')
                    )
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'normal',
                        'warning' => 'warning',
                        'danger' => 'critical',
                        'secondary' => 'error',
                    ]),

                TextColumn::make('reading_time')
                    ->label('Reading Time')
                    ->dateTime()
                    ->sortable()
                    ->since(),

                TextColumn::make('equipment.location')
                    ->label('Location')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Recorded At')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'normal' => 'Normal',
                        'warning' => 'Warning',
                        'critical' => 'Critical',
                        'error' => 'Error',
                    ])
                    ->multiple(),

                SelectFilter::make('sensor_type')
                    ->options([
                        'temperature' => 'Temperature',
                        'vibration' => 'Vibration',
                        'pressure' => 'Pressure',
                        'humidity' => 'Humidity',
                        'power' => 'Power Consumption',
                        'flow' => 'Flow Rate',
                    ])
                    ->multiple(),

                SelectFilter::make('equipment_id')
                    ->relationship('equipment', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Filter::make('reading_time')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('reading_time', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('reading_time', '<=', $date),
                            );
                    }),

                Filter::make('recent')
                    ->label('Recent (24 hours)')
                    ->query(fn (Builder $query): Builder => $query->where('reading_time', '>=', now()->subHours(24))),

                Filter::make('abnormal')
                    ->label('Abnormal Readings')
                    ->query(fn (Builder $query): Builder => $query->whereIn('status', ['warning', 'critical', 'error'])),
            ])
            ->defaultSort('reading_time', 'desc')
            ->poll('30s');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIotSensorReadings::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Readings are created via API, not manually
    }
}
