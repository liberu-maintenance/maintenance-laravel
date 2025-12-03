<?php

namespace App\Filament\App\Resources\CustomForms;

use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\CustomForms\Pages\ListCustomForms;
use App\Filament\App\Resources\CustomForms\Pages\CreateCustomForm;
use App\Filament\App\Resources\CustomForms\Pages\EditCustomForm;
use Filament\Forms;
use App\Models\CustomForm;
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
use Filament\Forms\Components\KeyValue;

class CustomFormResource extends Resource
{
    protected static ?string $model = CustomForm::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Form Management';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Form Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(3),
                        Select::make('category')
                            ->options([
                                'Maintenance Request' => 'Maintenance Request',
                                'Work Order' => 'Work Order',
                                'Equipment Inspection' => 'Equipment Inspection',
                                'Safety Report' => 'Safety Report',
                                'Incident Report' => 'Incident Report',
                                'Feedback' => 'Feedback',
                                'Other' => 'Other',
                            ])
                            ->searchable(),
                    ])->columns(2),

                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Toggle::make('is_public')
                            ->label('Public Form')
                            ->helperText('Public forms can be accessed by guests without login'),
                        KeyValue::make('settings')
                            ->label('Additional Settings')
                            ->keyLabel('Setting Name')
                            ->valueLabel('Setting Value'),
                    ])->columns(2),

                Section::make('Form Fields')
                    ->schema([
                        Repeater::make('fields')
                            ->relationship()
                            ->schema([
                                TextInput::make('label')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Field name for form processing (no spaces, use underscores)'),
                                Select::make('type')
                                    ->options([
                                        'text' => 'Text Input',
                                        'email' => 'Email',
                                        'number' => 'Number',
                                        'textarea' => 'Text Area',
                                        'select' => 'Select Dropdown',
                                        'checkbox' => 'Checkbox',
                                        'radio' => 'Radio Buttons',
                                        'date' => 'Date',
                                        'time' => 'Time',
                                        'file' => 'File Upload',
                                    ])
                                    ->default('text')
                                    ->required()
                                    ->reactive(),
                                Toggle::make('required')
                                    ->label('Required Field'),
                                TextInput::make('placeholder')
                                    ->maxLength(255),
                                Textarea::make('help_text')
                                    ->rows(2),
                                Textarea::make('options')
                                    ->label('Options (one per line)')
                                    ->rows(3)
                                    ->helperText('For select, checkbox, or radio fields')
                                    ->visible(fn ($get) => in_array($get('type'), ['select', 'checkbox', 'radio'])),
                                TextInput::make('order')
                                    ->numeric()
                                    ->default(0)
                                    ->label('Display Order'),
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                            ->addActionLabel('Add Form Field')
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
                BadgeColumn::make('is_active')
                    ->label('Status')
                    ->colors([
                        'success' => true,
                        'secondary' => false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive'),
                BadgeColumn::make('is_public')
                    ->label('Access')
                    ->colors([
                        'warning' => true,
                        'success' => false,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? 'Public' : 'Private'),
                TextColumn::make('fields_count')
                    ->label('Fields')
                    ->counts('fields')
                    ->sortable(),
                TextColumn::make('submissions_count')
                    ->label('Submissions')
                    ->counts('submissions')
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
                SelectFilter::make('category')
                    ->options([
                        'Maintenance Request' => 'Maintenance Request',
                        'Work Order' => 'Work Order',
                        'Equipment Inspection' => 'Equipment Inspection',
                        'Safety Report' => 'Safety Report',
                        'Incident Report' => 'Incident Report',
                        'Feedback' => 'Feedback',
                        'Other' => 'Other',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Active'),
                TernaryFilter::make('is_public')
                    ->label('Public'),
            ])
            ->recordActions([
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->action(function ($record) {
                        $newForm = $record->duplicate();
                        return redirect()->route('filament.app.resources.custom-forms.edit', $newForm);
                    }),
                Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => route('custom-form.show', $record))
                    ->openUrlInNewTab(),
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
            'index' => ListCustomForms::route('/'),
            'create' => CreateCustomForm::route('/create'),
            'edit' => EditCustomForm::route('/{record}/edit'),
        ];
    }
}