<?php

namespace App\Filament\App\Widgets;

use App\Models\WorkOrder;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;

class RecentWorkOrdersWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Work Orders';

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                WorkOrder::query()
                    ->when(filament()->getTenant()?->id, fn($q, $teamId) => $q->where('team_id', $teamId))
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(40),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'rejected' => 'danger',
                        'in_progress' => 'primary',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),

                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'warning',
                        'high' => 'orange',
                        'urgent' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('location')
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('guest_name')
                    ->label('Submitted By')
                    ->limit(25)
                    ->toggleable(),

                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->since()
                    ->tooltip(fn (WorkOrder $record): string => $record->submitted_at->format('F j, Y g:i A')),
            ])
            ->actions([
                Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn (WorkOrder $record): string => route('filament.app.resources.work-orders.work-orders.view', $record))
                    ->openUrlInNewTab(),

                Action::make('edit')
                    ->icon('heroicon-m-pencil')
                    ->url(fn (WorkOrder $record): string => route('filament.app.resources.work-orders.work-orders.edit', $record))
                    ->visible(fn (WorkOrder $record): bool => $record->status !== 'completed'),
            ])
            ->emptyStateHeading('No work orders yet')
            ->emptyStateDescription('Work orders will appear here once they are submitted.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->defaultSort('submitted_at', 'desc')
            ->striped()
            ->paginated(false);
    }
}