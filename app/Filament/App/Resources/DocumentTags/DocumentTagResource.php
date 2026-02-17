<?php

namespace App\Filament\App\Resources\DocumentTags;

use App\Filament\App\Resources\DocumentTags\Pages\ListDocumentTags;
use App\Filament\App\Resources\DocumentTags\Pages\CreateDocumentTag;
use App\Filament\App\Resources\DocumentTags\Pages\EditDocumentTag;
use App\Models\DocumentTag;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ColorPicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class DocumentTagResource extends Resource
{
    protected static ?string $model = DocumentTag::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    protected static string | \UnitEnum | null $navigationGroup = 'Documentation';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Tags';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Tag Name')
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->rows(3)
                    ->label('Description'),
                ColorPicker::make('color')
                    ->label('Tag Color')
                    ->default('#3b82f6'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')
                    ->label('Color'),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Tag Name'),
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->label('Slug')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('documents_count')
                    ->counts('documents')
                    ->label('Documents')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Created')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentTags::route('/'),
            'create' => CreateDocumentTag::route('/create'),
            'edit' => EditDocumentTag::route('/{record}/edit'),
        ];
    }
}
