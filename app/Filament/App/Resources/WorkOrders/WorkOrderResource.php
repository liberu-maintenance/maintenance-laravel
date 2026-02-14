<?php

namespace App\Filament\App\Resources\WorkOrders;


use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\App\Resources\WorkOrders\WorkOrderResource\Pages\ListWorkOrders;
use App\Filament\App\Resources\WorkOrders\WorkOrderResource\Pages\CreateWorkOrder;
use App\Filament\App\Resources\WorkOrders\WorkOrderResource\Pages\EditWorkOrder;
use App\Filament\App\Resources\WorkOrders\WorkOrderResource\Pages;
use App\Models\WorkOrder;
use App\Models\Equipment;
use App\Models\MaintenanceSchedule;
use App\Models\Checklist;
use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use Filament\Actions;
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
                ->columns(1)
                ->components([
                Section::make('Work Order Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Textarea::make('description')
                                    ->required()
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Select::make('priority')
                                    ->options([
                                        'low' => 'Low',
                                        'medium' => 'Medium',
                                        'high' => 'High',
                                        'urgent' => 'Urgent',
                                    ])
                                    ->required()
                                    ->default('medium'),

                                Select::make('status')
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

                Section::make('Location & Equipment')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('location')
                                    ->maxLength(255),

                                Select::make('equipment_id')
                                    ->label('Equipment')
                                    ->relationship('equipment', 'name')
                                    ->searchable()
                                    ->preload(),

                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->relationship('customer', 'name', function ($query) {
                                        $query->customers()->active();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Select::make('type')
                                            ->options([
                                                'customer' => 'Customer',
                                                'both' => 'Both (Customer & Supplier)',
                                            ])
                                            ->default('customer')
                                            ->required(),
                                        TextInput::make('contact_person')
                                            ->maxLength(255),
                                        TextInput::make('email')
                                            ->email()
                                            ->maxLength(255),
                                        TextInput::make('phone_number')
                                            ->tel()
                                            ->maxLength(255),
                                    ]),
                            ]),
                    ]),

                Section::make('Assignment & Schedule')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('team_id')
                                    ->label('Team')
                                    ->relationship('team', 'name')
                                    ->searchable()
                                    ->preload(),

                                Select::make('assigned_to')
                                    ->label('Assigned To')
                                    ->relationship('assignedTo', 'name')
                                    ->searchable()
                                    ->preload(),

                                DateTimePicker::make('due_date')
                                    ->label('Due Date'),

                                DateTimePicker::make('started_at')
                                    ->label('Started At')
                                    ->disabled(fn ($get) => !in_array($get('status'), ['in_progress', 'completed'])),

                                DateTimePicker::make('completed_at')
                                    ->label('Completed At')
                                    ->disabled(fn ($get) => $get('status') !== 'completed'),
                            ]),
                    ]),

                Section::make('Labor Hours')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('estimated_hours')
                                    ->label('Estimated Hours')
                                    ->numeric()
                                    ->minValue(0)
                                    ->suffix('hours'),

                                TextInput::make('actual_hours')
                                    ->label('Actual Hours')
                                    ->numeric()
                                    ->minValue(0)
                                    ->suffix('hours')
                                    ->disabled(fn ($get) => $get('status') !== 'completed'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Related Records')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('maintenance_schedule_id')
                                    ->label('Maintenance Schedule')
                                    ->relationship('maintenanceSchedule', 'name')
                                    ->searchable()
                                    ->preload(),

                                Select::make('checklist_id')
                                    ->label('Checklist')
                                    ->relationship('checklist', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ]),

                Section::make('Guest Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('guest_name')
                                    ->label('Guest Name')
                                    ->maxLength(255),

                                TextInput::make('guest_email')
                                    ->label('Guest Email')
                                    ->email()
                                    ->maxLength(255),

                                TextInput::make('guest_phone')
                                    ->label('Guest Phone')
                                    ->tel()
                                    ->maxLength(255),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Review Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('reviewed_by')
                                    ->label('Reviewed By')
                                    ->relationship('reviewer', 'name')
                                    ->searchable()
                                    ->preload(),

                                DateTimePicker::make('reviewed_at')
                                    ->label('Reviewed At'),
                            ]),

                        Textarea::make('notes')
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

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->color(fn ($record) => 
                        $record->due_date && 
                        $record->due_date < now() && 
                        !in_array($record->status, ['completed', 'rejected']) 
                            ? 'danger' 
                            : null
                    ),

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

                TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('estimated_hours')
                    ->label('Est. Hours')
                    ->suffix(' hrs')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('actual_hours')
                    ->label('Actual Hours')
                    ->suffix(' hrs')
                    ->toggleable(isToggledHiddenByDefault: true),

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

                SelectFilter::make('assigned_to')
                    ->label('Assigned To')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
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
            ->toolbarActions([
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
            'index' => ListWorkOrders::route('/'),
            'create' => CreateWorkOrder::route('/create'),
            'edit' => EditWorkOrder::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $pendingCount = static::getModel()::where('status', 'pending')->count();
        $overdueCount = static::getModel()::overdue()->count();
        
        if ($overdueCount > 0) {
            return "{$overdueCount} overdue";
        }
        
        return $pendingCount > 0 ? (string) $pendingCount : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $overdueCount = static::getModel()::overdue()->count();
        
        if ($overdueCount > 0) {
            return 'danger';
        }
        
        return static::getModel()::where('status', 'pending')->count() > 0 ? 'warning' : 'success';
    }
}
