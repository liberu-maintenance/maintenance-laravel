<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\CustomForms\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\CustomForms\CustomFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomForms extends ListRecords
{
    #[\Override]
    protected static string $resource = CustomFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
