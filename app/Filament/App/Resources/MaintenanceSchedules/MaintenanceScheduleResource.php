<?php

namespace App\Filament\App\Resources\MaintenanceSchedules;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\MaintenanceSchedules\Pages\ListMaintenanceSchedules;
use App\Filament\App\Resources\MaintenanceSchedules\Pages\CreateMaintenanceSchedule;
use App\Filament\App\Resources\MaintenanceSchedules\Pages\EditMaintenanceSchedule;
use Filament\Forms;
use App\Models\MaintenanceSchedule;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceScheduleResource extends Resource
{
    protected static ?string $model = MaintenanceSchedule::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string | \UnitEnum | null $navigationGroup = 'Maintenance Management';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Schedule Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(3),
                        Select::make('equipment_id')
                            ->relationship('equipment', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(1),

                Section::make('Frequency Settings')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('frequency_type')
                                    ->options([
                                        'daily' => 'Daily',
                                        'weekly' => 'Weekly',
                                        'monthly' => 'Monthly',
                                        'yearly' => 'Yearly',
                                        'hours' => 'Hours',
                                    ])
                                    ->default('monthly')
                                    ->required(),
                                TextInput::make('frequency_value')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(1),
                                TextInput::make('estimated_duration')
                                    ->numeric()
                                    ->suffix('minutes')
                                    ->helperText('Estimated time to complete this maintenance'),
                            ]),
                    ]),

                Section::make('Scheduling')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('next_due_date')
                                    ->required(),
                                DatePicker::make('last_completed_date'),
                            ]),
                    ]),

                Section::make('Assignment & Priority')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('priority')
                                    ->options([
                                        'low' => 'Low',
                                        'medium' => 'Medium',
                                        'high' => 'High',
                                        'critical' => 'Critical',
                                    ])
                                    ->default('medium')
                                    ->required(),
                                Select::make('status')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                        'completed' => 'Completed',
                                    ])
                                    ->default('active')
                                    ->required(),
                                Select::make('assigned_to')
                                    ->relationship('assignedUser', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ]),

                Section::make('Instructions & Checklist')
                    ->schema([
                        Textarea::make('instructions')
                            ->rows(4)
                            ->helperText('Detailed instructions for performing this maintenance'),
                        Select::make('checklist_id')
                            ->relationship('checklist', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Optional checklist to follow during maintenance'),
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
                TextColumn::make('equipment.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('frequency_display')
                    ->label('Frequency')
                    ->getStateUsing(fn ($record) => "Every {$record->frequency_value} " . ucfirst($record->frequency_type))
                    ->sortable(),
                TextColumn::make('next_due_date')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->next_due_date < now() ? 'danger' : ($record->next_due_date < now()->addDays(7) ? 'warning' : 'success')),
                TextColumn::make('last_completed_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('priority')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                        'danger' => 'critical',
                    ]),
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'completed',
                        'secondary' => 'inactive',
                    ]),
                TextColumn::make('assignedUser.name')
                    ->label('Assigned To')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('estimated_duration')
                    ->suffix(' min')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'completed' => 'Completed',
                    ]),
                SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ]),
                Tables\Filters\Filter::make('overdue')
                    ->query(fn (Builder $query): Builder => $query->overdue())
                    ->label('Overdue'),
                Tables\Filters\Filter::make('due_soon')
                    ->query(fn (Builder $query): Builder => $query->dueSoon())
                    ->label('Due Soon (7 days)'),
            ])
            ->recordActions([
                Action::make('mark_completed')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function ($record) {
                        $record->markCompleted();
                    })
                    ->visible(fn ($record) => $record->status === 'active'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('next_due_date');
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
            'index' => ListMaintenanceSchedules::route('/'),
            'create' => CreateMaintenanceSchedule::route('/create'),
            'edit' => EditMaintenanceSchedule::route('/{record}/edit'),
        ];
    }
}