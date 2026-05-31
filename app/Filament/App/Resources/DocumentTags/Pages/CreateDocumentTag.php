<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\DocumentTags\Pages;

use App\Filament\App\Resources\DocumentTags\DocumentTagResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDocumentTag extends CreateRecord
{
    #[\Override]
    protected static string $resource = DocumentTagResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['team_id'] = Auth::user()->currentTeam?->id;

        return $data;
    }
}
