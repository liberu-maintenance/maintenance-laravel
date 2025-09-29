<?php

namespace App\Filament\App\Resources\WorkOrders\WorkOrderResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\WorkOrders\WorkOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkOrders extends ListRecords
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}