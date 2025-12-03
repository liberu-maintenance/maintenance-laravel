<?php

namespace App\Filament\App\Resources\Checklists;

use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\Checklists\Pages\ListChecklists;
use App\Filament\App\Resources\Checklists\Pages\CreateChecklist;
use App\Filament\App\Resources\Checklists\Pages\EditChecklist;
use Filament\Forms;
use App\Models\Checklist;
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
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Builder;

class ChecklistResource extends Resource
{
    protected static ?string $model = Checklist::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string | \UnitEnum | null $navigationGroup = 'Maintenance Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Checklist Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(3),
                        Select::make('category')
                            ->options([
                                'Safety Inspection' => 'Safety Inspection',
                                'Preventive Maintenance' => 'Preventive Maintenance',
                                'Equipment Check' => 'Equipment Check',
                                'Quality Control' => 'Quality Control',
                                'Compliance Audit' => 'Compliance Audit',
                                'Emergency Procedure' => 'Emergency Procedure',
                                'Other' => 'Other',
                            ])
                            ->searchable(),
                        Select::make('equipment_id')
                            ->relationship('equipment', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Optional: Link this checklist to specific equipment'),
                    ])->columns(2),

                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_template')
                            ->label('Is Template')
                            ->helperText('Templates can be reused for multiple equipment or schedules'),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'draft' => 'Draft',
                            ])
                            ->default('active')
                            ->required(),
                    ])->columns(2),

                Section::make('Checklist Items')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('description')
                                    ->rows(2),
                                Select::make('type')
                                    ->options([
                                        'checkbox' => 'Checkbox',
                                        'text' => 'Text Input',
                                        'number' => 'Number Input',
                                        'select' => 'Select Dropdown',
                                        'textarea' => 'Text Area',
                                        'date' => 'Date',
                                        'time' => 'Time',
                                    ])
                                    ->default('checkbox')
                                    ->required()
                                    ->reactive(),
                                Toggle::make('required')
                                    ->label('Required'),
                                TextInput::make('options')
                                    ->label('Options (comma separated)')
                                    ->helperText('For select type items, enter options separated by commas')
                                    ->visible(fn ($get) => $get('type') === 'select'),
                                TextInput::make('order')
                                    ->numeric()
                                    ->default(0)
                                    ->label('Order'),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->addActionLabel('Add Checklist Item')
                            ->reorderable('order'),
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
                TextColumn::make('category')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('equipment.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('is_template')
                    ->label('Template')
                    ->colors([
                        'success' => true,
                        'secondary' => false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'draft',
                        'secondary' => 'inactive',
                    ]),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->sortable(),
                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'draft' => 'Draft',
                    ]),
                SelectFilter::make('category')
                    ->options([
                        'Safety Inspection' => 'Safety Inspection',
                        'Preventive Maintenance' => 'Preventive Maintenance',
                        'Equipment Check' => 'Equipment Check',
                        'Quality Control' => 'Quality Control',
                        'Compliance Audit' => 'Compliance Audit',
                        'Emergency Procedure' => 'Emergency Procedure',
                        'Other' => 'Other',
                    ]),
                TernaryFilter::make('is_template')
                    ->label('Is Template'),
            ])
            ->recordActions([
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->action(function ($record) {
                        $newChecklist = $record->duplicate();
                        return redirect()->route('filament.app.resources.checklists.edit', $newChecklist);
                    }),
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
            'index' => ListChecklists::route('/'),
            'create' => CreateChecklist::route('/create'),
            'edit' => EditChecklist::route('/{record}/edit'),
        ];
    }
}