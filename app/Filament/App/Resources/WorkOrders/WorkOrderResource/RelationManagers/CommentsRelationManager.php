<?php

namespace App\Filament\App\Resources\WorkOrders\WorkOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Comments & Notes';

    protected static ?string $recordTitleAttribute = 'comment';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('comment')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull()
                    ->label('Add Comment or Note'),

                Checkbox::make('is_internal')
                    ->label('Internal Note (not visible to guests)')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('comment')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Author')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('comment')
                    ->wrap()
                    ->limit(100)
                    ->searchable(),

                IconColumn::make('is_internal')
                    ->label('Internal')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Posted')
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_internal')
                    ->label('Internal Notes')
                    ->placeholder('All comments')
                    ->trueLabel('Internal only')
                    ->falseLabel('Public only'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No comments yet')
            ->emptyStateDescription('Add comments to document work progress and outcomes.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }
}
