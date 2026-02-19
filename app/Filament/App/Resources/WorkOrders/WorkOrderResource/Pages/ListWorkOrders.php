<?php

namespace App\Filament\App\Resources\WorkOrders\WorkOrderResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\WorkOrders\WorkOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListWorkOrders extends ListRecords
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(fn () => \App\Models\WorkOrder::count()),
            
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => \App\Models\WorkOrder::where('status', 'pending')->count())
                ->badgeColor('warning'),
            
            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved'))
                ->badge(fn () => \App\Models\WorkOrder::where('status', 'approved')->count())
                ->badgeColor('success'),
            
            'in_progress' => Tab::make('In Progress')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'in_progress'))
                ->badge(fn () => \App\Models\WorkOrder::where('status', 'in_progress')->count())
                ->badgeColor('info'),
            
            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
                ->badge(fn () => \App\Models\WorkOrder::where('status', 'completed')->count())
                ->badgeColor('success'),
            
            'overdue' => Tab::make('Overdue')
                ->modifyQueryUsing(fn (Builder $query) => $query->overdue())
                ->badge(fn () => \App\Models\WorkOrder::overdue()->count())
                ->badgeColor('danger'),
        ];
    }
}