<?php

namespace App\Filament\App\Resources\WorkOrders;


use App\Filament\App\Resources\WorkOrders\WorkOrderResource\Pages;
use App\Models\WorkOrder;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use App\Models\Checklist;
use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string | \UnitEnum | null $navigationGroup = 'Maintenance';

    protected static ?string $navigationLabel = 'Work Orders';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
                ->schema([
                Forms\Components\Section::make('Work Order Details')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('description')
                                    ->required()
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('priority')
                                    ->options([
                                        'low' => 'Low',
                                        'medium' => 'Medium',
                                        'high' => 'High',
                                        'urgent' => 'Urgent',
                                    ])
                                    ->required()
                                    ->default('medium'),

                                Forms\Components\Select::make('status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                        'in_progress' => 'In Progress',
                                        'completed' => 'Completed',
                                    ])
                                    ->required()
                                    ->default('pending'),
                            ]),
                    ]),

                Forms\Components\Section::make('Location & Equipment')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('location')
                                    ->maxLength(255),

                                Forms\Components\Select::make('equipment_id')
                                    ->label('Equipment')
                                    ->relationship('equipment', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ]),

                Forms\Components\Section::make('Related Records')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('maintenance_schedule_id')
                                    ->label('Maintenance Schedule')
                                    ->relationship('maintenanceSchedule', 'name')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('checklist_id')
                                    ->label('Checklist')
                                    ->relationship('checklist', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ]),

                Forms\Components\Section::make('Guest Information')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('guest_name')
                                    ->label('Guest Name')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('guest_email')
                                    ->label('Guest Email')
                                    ->email()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('guest_phone')
                                    ->label('Guest Phone')
                                    ->tel()
                                    ->maxLength(255),
                            ]),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Review Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('reviewed_by')
                                    ->label('Reviewed By')
                                    ->relationship('reviewer', 'name')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\DateTimePicker::make('reviewed_at')
                                    ->label('Reviewed At'),
                            ]),

                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'warning',
                        'high' => 'danger',
                        'urgent' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('location')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('equipment.name')
                    ->label('Equipment')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('guest_name')
                    ->label('Guest')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('reviewer.name')
                    ->label('Reviewed By')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                    ]),

                SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),

                SelectFilter::make('equipment_id')
                    ->label('Equipment')
                    ->relationship('equipment', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (WorkOrder $record) {
                        $record->update([
                            'status' => 'approved',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                    })
                    ->visible(fn (WorkOrder $record) => $record->status === 'pending'),

                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function (WorkOrder $record) {
                        $record->update([
                            'status' => 'rejected',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                    })
                    ->visible(fn (WorkOrder $record) => $record->status === 'pending'),

                Action::make('start_progress')
                    ->label('Start Work')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->action(function (WorkOrder $record) {
                        $record->update(['status' => 'in_progress']);
                    })
                    ->visible(fn (WorkOrder $record) => $record->status === 'approved'),

                Action::make('complete')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->action(function (WorkOrder $record) {
                        $record->update(['status' => 'completed']);
                    })
                    ->visible(fn (WorkOrder $record) => $record->status === 'in_progress'),

                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListWorkOrders::route('/'),
            'create' => Pages\CreateWorkOrder::route('/create'),
            'edit' => Pages\EditWorkOrder::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::where('status', 'pending')->count() > 0 ? 'warning' : 'success';
    }
}