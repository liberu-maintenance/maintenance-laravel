<?php

namespace App\Filament\App\Resources\Opportunities\Pages;

use App\Filament\App\Resources\Opportunities\OpportunityResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOpportunity extends CreateRecord
{
    protected static string $resource = OpportunityResource::class;
}
