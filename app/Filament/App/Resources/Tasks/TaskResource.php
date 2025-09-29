<?php

namespace App\Filament\App\Resources\Tasks;

use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions;
use App\Filament\App\Resources\Tasks\Pages\ListTasks;
use App\Filament\App\Resources\Tasks\Pages\CreateTask;
use App\Filament\App\Resources\Tasks\Pages\EditTask;
use Filament\Forms;
use App\Models\Task;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\App\Resources\TaskResource\Pages;
use App\Filament\App\Resources\TaskResource\RelationManagers;
use App\Notifications\TaskAssignedNotification;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Name'),
                Textarea::make('description')->label('Description'),
                DatePicker::make('due_date')->label('Due Date'),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->label('Status'),
                Select::make('contact_id')
                    ->relationship('contact', 'name')
                    ->label('Contact'),
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->label('Company'),
                Select::make('opportunity_id')
                    ->relationship('opportunity', 'name')
                    ->label('Opportunity'),
                Select::make('assigned_to')
                    ->relationship('assignedUser', 'name')
                    ->label('Assigned To')
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $record) {
                        if ($state && $record) {
                            $user = User::find($state);
                            if ($user) {
                                $user->notify(new TaskAssignedNotification($record, 'task'));
                            }
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status'),
                TextColumn::make('contact_id'),
                TextColumn::make('company_id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('opportunity_id'),
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
            ]);
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
            'index' => ListTasks::route('/'),
            'create' => CreateTask::route('/create'),
            'edit' => EditTask::route('/{record}/edit'),
        ];
    }
}
