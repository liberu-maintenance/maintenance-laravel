<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Vendors\Pages;

use App\Filament\App\Resources\Vendors\VendorResource;
use Filament\Resources\Pages\EditRecord;

class EditVendor extends EditRecord
{
    #[\Override]
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}
