<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Opportunities\Pages;

use App\Filament\App\Resources\Opportunities\OpportunityResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOpportunity extends CreateRecord
{
    #[\Override]
    protected static string $resource = OpportunityResource::class;
}
