<?php

namespace App\Filament\App\Resources\Equipment;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\Equipment\Pages\ListEquipment;
use App\Filament\App\Resources\Equipment\Pages\CreateEquipment;
use App\Filament\App\Resources\Equipment\Pages\EditEquipment;
use Filament\Forms;
use App\Models\Equipment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static string | \UnitEnum | null $navigationGroup = 'Asset Management';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(3),
                        TextInput::make('serial_number')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('model')
                            ->maxLength(255),
                        TextInput::make('manufacturer')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Classification')
                    ->schema([
                        Select::make('category')
                            ->options([
                                'HVAC' => 'HVAC',
                                'Electrical' => 'Electrical',
                                'Plumbing' => 'Plumbing',
                                'Mechanical' => 'Mechanical',
                                'IT Equipment' => 'IT Equipment',
                                'Safety Equipment' => 'Safety Equipment',
                                'Vehicles' => 'Vehicles',
                                'Other' => 'Other',
                            ])
                            ->searchable(),
                        TextInput::make('location')
                            ->maxLength(255),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'under_maintenance' => 'Under Maintenance',
                                'retired' => 'Retired',
                            ])
                            ->default('active')
                            ->required(),
                        Select::make('criticality')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'critical' => 'Critical',
                            ])
                            ->default('medium')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Purchase Information')
                    ->schema([
                        DatePicker::make('purchase_date'),
                        DatePicker::make('warranty_expiry'),
                        Select::make('company_id')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(3),

                Forms\Components\Section::make('Additional Notes')
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
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('serial_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'under_maintenance',
                        'danger' => 'retired',
                        'secondary' => 'inactive',
                    ]),
                BadgeColumn::make('criticality')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'danger' => 'critical',
                    ]),
                TextColumn::make('manufacturer')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('model')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('purchase_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('warranty_expiry')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('company.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'under_maintenance' => 'Under Maintenance',
                        'retired' => 'Retired',
                    ]),
                SelectFilter::make('criticality')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ]),
                SelectFilter::make('category')
                    ->options([
                        'HVAC' => 'HVAC',
                        'Electrical' => 'Electrical',
                        'Plumbing' => 'Plumbing',
                        'Mechanical' => 'Mechanical',
                        'IT Equipment' => 'IT Equipment',
                        'Safety Equipment' => 'Safety Equipment',
                        'Vehicles' => 'Vehicles',
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
            'index' => ListEquipment::route('/'),
            'create' => CreateEquipment::route('/create'),
            'edit' => EditEquipment::route('/{record}/edit'),
        ];
    }
}