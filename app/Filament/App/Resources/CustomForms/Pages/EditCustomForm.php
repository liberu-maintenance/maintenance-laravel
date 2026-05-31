<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\CustomForms\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\CustomForms\CustomFormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomForm extends EditRecord
{
    #[\Override]
    protected static string $resource = CustomFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
