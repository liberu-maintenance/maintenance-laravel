<?php

namespace App\Filament\App\Resources\WorkOrders\WorkOrderResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use App\Filament\App\Resources\WorkOrders\WorkOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class EditWorkOrder extends EditRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve Work Order')
                ->modalDescription('Are you sure you want to approve this work order?')
                ->action(function () {
                    $this->record->update([
                        'status' => 'approved',
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);
                    
                    Notification::make()
                        ->success()
                        ->title('Work order approved')
                        ->body('The work order has been approved and is ready to be started.')
                        ->send();
                })
                ->visible(fn () => $this->record->status === 'pending'),

            Action::make('reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reject Work Order')
                ->form([
                    Textarea::make('notes')
                        ->label('Rejection Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'rejected',
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                        'notes' => $data['notes'],
                    ]);
                    
                    // Add comment to log rejection
                    $this->record->comments()->create([
                        'user_id' => auth()->id(),
                        'comment' => 'Work order rejected: ' . $data['notes'],
                        'is_internal' => false,
                    ]);
                    
                    Notification::make()
                        ->warning()
                        ->title('Work order rejected')
                        ->body('The work order has been rejected.')
                        ->send();
                })
                ->visible(fn () => $this->record->status === 'pending'),

            Action::make('start_work')
                ->label('Start Work')
                ->icon('heroicon-o-play')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Start Work')
                ->modalDescription('This will mark the work order as in progress and set the start time.')
                ->action(function () {
                    $this->record->update([
                        'status' => 'in_progress',
                        'started_at' => now(),
                    ]);
                    
                    Notification::make()
                        ->success()
                        ->title('Work started')
                        ->body('The work order is now in progress.')
                        ->send();
                })
                ->visible(fn () => $this->record->status === 'approved'),

            Action::make('complete')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Complete Work Order')
                ->form([
                    Textarea::make('completion_notes')
                        ->label('Completion Notes')
                        ->helperText('Document the work performed and outcomes.')
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);
                    
                    // Add completion comment if provided
                    if (!empty($data['completion_notes'])) {
                        $this->record->comments()->create([
                            'user_id' => auth()->id(),
                            'comment' => 'Work completed: ' . $data['completion_notes'],
                            'is_internal' => false,
                        ]);
                    }
                    
                    Notification::make()
                        ->success()
                        ->title('Work order completed')
                        ->body('The work order has been marked as completed.')
                        ->send();
                })
                ->visible(fn () => $this->record->status === 'in_progress'),

            Action::make('reopen')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Reopen Work Order')
                ->form([
                    Textarea::make('reopen_reason')
                        ->label('Reason for Reopening')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'in_progress',
                    ]);
                    
                    $this->record->comments()->create([
                        'user_id' => auth()->id(),
                        'comment' => 'Work order reopened: ' . $data['reopen_reason'],
                        'is_internal' => false,
                    ]);
                    
                    Notification::make()
                        ->warning()
                        ->title('Work order reopened')
                        ->body('The work order has been reopened.')
                        ->send();
                })
                ->visible(fn () => $this->record->status === 'completed'),

            DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Work order updated')
            ->body('The work order has been updated successfully.');
    }
}