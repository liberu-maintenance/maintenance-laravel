<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Opportunities\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\Opportunities\OpportunityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOpportunities extends ListRecords
{
    #[\Override]
    protected static string $resource = OpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
