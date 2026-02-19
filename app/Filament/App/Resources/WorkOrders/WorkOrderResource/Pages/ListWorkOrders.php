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
        $cacheKey = 'work_orders.tab_counts';
        
        $counts = cache()->remember($cacheKey, now()->addMinutes(5), function () {
            return [
                'all' => \App\Models\WorkOrder::count(),
                'pending' => \App\Models\WorkOrder::where('status', 'pending')->count(),
                'approved' => \App\Models\WorkOrder::where('status', 'approved')->count(),
                'in_progress' => \App\Models\WorkOrder::where('status', 'in_progress')->count(),
                'completed' => \App\Models\WorkOrder::where('status', 'completed')->count(),
                'overdue' => \App\Models\WorkOrder::overdue()->count(),
            ];
        });
        
        return [
            'all' => Tab::make('All')
                ->badge($counts['all']),
            
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge($counts['pending'])
                ->badgeColor('warning'),
            
            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved'))
                ->badge($counts['approved'])
                ->badgeColor('success'),
            
            'in_progress' => Tab::make('In Progress')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'in_progress'))
                ->badge($counts['in_progress'])
                ->badgeColor('info'),
            
            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
                ->badge($counts['completed'])
                ->badgeColor('success'),
            
            'overdue' => Tab::make('Overdue')
                ->modifyQueryUsing(fn (Builder $query) => $query->overdue())
                ->badge($counts['overdue'])
                ->badgeColor('danger'),
        ];
    }
}