<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Opportunities\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\Opportunities\OpportunityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOpportunity extends EditRecord
{
    #[\Override]
    protected static string $resource = OpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
