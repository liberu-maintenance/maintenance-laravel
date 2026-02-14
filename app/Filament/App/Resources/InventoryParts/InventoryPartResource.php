<?php

namespace App\Filament\App\Resources\InventoryParts;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\InventoryParts\Pages\ListInventoryParts;
use App\Filament\App\Resources\InventoryParts\Pages\CreateInventoryPart;
use App\Filament\App\Resources\InventoryParts\Pages\EditInventoryPart;
use Filament\Forms;
use App\Models\InventoryPart;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class InventoryPartResource extends Resource
{
    protected static ?string $model = InventoryPart::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cube';

    protected static string | \UnitEnum | null $navigationGroup = 'Inventory Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Parts';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('part_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(3),
                    ])->columns(2),

                Section::make('Classification')
                    ->schema([
                        Select::make('category')
                            ->options([
                                'Mechanical' => 'Mechanical',
                                'Electrical' => 'Electrical',
                                'Hydraulic' => 'Hydraulic',
                                'Pneumatic' => 'Pneumatic',
                                'Consumables' => 'Consumables',
                                'Fasteners' => 'Fasteners',
                                'Filters' => 'Filters',
                                'Lubricants' => 'Lubricants',
                                'Safety' => 'Safety',
                                'Other' => 'Other',
                            ])
                            ->searchable(),
                        TextInput::make('unit_of_measure')
                            ->default('piece')
                            ->maxLength(255),
                        TextInput::make('location')
                            ->maxLength(255)
                            ->helperText('Default storage location'),
                    ])->columns(3),

                Section::make('Pricing & Inventory')
                    ->schema([
                        TextInput::make('unit_cost')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->step(0.01),
                        TextInput::make('reorder_level')
                            ->numeric()
                            ->default(0)
                            ->helperText('Minimum quantity before reordering')
                            ->integer(),
                        TextInput::make('reorder_quantity')
                            ->numeric()
                            ->default(0)
                            ->helperText('Quantity to order when stock is low')
                            ->integer(),
                    ])->columns(3),

                Section::make('Supplier Information')
                    ->schema([
                        TextInput::make('supplier')
                            ->maxLength(255),
                        TextInput::make('lead_time_days')
                            ->numeric()
                            ->integer()
                            ->suffix('days')
                            ->helperText('Expected delivery time'),
                    ])->columns(2),

                Section::make('Additional Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->rows(4),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('part_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_quantity')
                    ->label('Stock')
                    ->getStateUsing(fn ($record) => $record->total_quantity)
                    ->badge()
                    ->color(fn ($state, $record) => $state <= $record->reorder_level ? 'danger' : 'success'),
                TextColumn::make('available_quantity')
                    ->label('Available')
                    ->getStateUsing(fn ($record) => $record->available_quantity),
                TextColumn::make('unit_cost')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('location')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('supplier')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reorder_level')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('unit_of_measure')
                    ->label('UOM')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'Mechanical' => 'Mechanical',
                        'Electrical' => 'Electrical',
                        'Hydraulic' => 'Hydraulic',
                        'Pneumatic' => 'Pneumatic',
                        'Consumables' => 'Consumables',
                        'Fasteners' => 'Fasteners',
                        'Filters' => 'Filters',
                        'Lubricants' => 'Lubricants',
                        'Safety' => 'Safety',
                        'Other' => 'Other',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('part_number');
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
            'index' => ListInventoryParts::route('/'),
            'create' => CreateInventoryPart::route('/create'),
            'edit' => EditInventoryPart::route('/{record}/edit'),
        ];
    }
}
