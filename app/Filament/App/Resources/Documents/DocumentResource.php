<?php

namespace App\Filament\App\Resources\Documents;

use App\Filament\App\Resources\Documents\Pages\ListDocuments;
use App\Filament\App\Resources\Documents\Pages\CreateDocument;
use App\Filament\App\Resources\Documents\Pages\EditDocument;
use App\Filament\App\Resources\Documents\Pages\ViewDocument;
use App\Models\Document;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Documentation';

    protected static ?int $navigationSort = 1;

    // Allowed file types for document upload
    const ACCEPTED_FILE_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/*',
    ];

    // Allowed documentable model classes
    const ALLOWED_DOCUMENTABLE_TYPES = [
        'App\\Models\\Equipment' => 'Equipment',
        'App\\Models\\WorkOrder' => 'Work Order',
        'App\\Models\\MaintenanceSchedule' => 'Maintenance Schedule',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Document Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Document Name'),
                        Textarea::make('description')
                            ->rows(3)
                            ->label('Description'),
                        Select::make('document_type')
                            ->required()
                            ->options([
                                'manual' => 'Manual',
                                'service_record' => 'Service Record',
                                'compliance' => 'Compliance Document',
                                'procedure' => 'Procedure',
                                'checklist' => 'Checklist',
                                'report' => 'Report',
                                'certificate' => 'Certificate',
                                'other' => 'Other',
                            ])
                            ->searchable()
                            ->label('Document Type'),
                        TextInput::make('version')
                            ->default('1.0')
                            ->required()
                            ->label('Version'),
                        Select::make('status')
                            ->required()
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'archived' => 'Archived',
                                'obsolete' => 'Obsolete',
                            ])
                            ->default('active')
                            ->label('Status'),
                    ])->columns(2),

                Section::make('File Upload')
                    ->schema([
                        FileUpload::make('file_path')
                            ->required()
                            ->disk('local')
                            ->directory('documents')
                            ->acceptedFileTypes(self::ACCEPTED_FILE_TYPES)
                            ->maxSize(10240) // 10MB
                            ->label('Upload Document')
                            ->helperText('Accepted formats: PDF, Word, Images (Max 10MB)'),
                    ]),

                Section::make('Compliance & Regulatory')
                    ->schema([
                        TextInput::make('compliance_standard')
                            ->maxLength(255)
                            ->label('Compliance Standard')
                            ->helperText('e.g., ISO 9001, OSHA, FDA, CE'),
                        DatePicker::make('effective_date')
                            ->label('Effective Date'),
                        DatePicker::make('expiry_date')
                            ->label('Expiry Date'),
                        DatePicker::make('review_date')
                            ->label('Next Review Date'),
                        Select::make('approval_status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->label('Approval Status'),
                    ])->columns(2),

                Section::make('Relationships')
                    ->schema([
                        Select::make('documentable_type')
                            ->label('Attach To')
                            ->options(self::ALLOWED_DOCUMENTABLE_TYPES)
                            ->reactive()
                            ->searchable(),
                        Select::make('documentable_id')
                            ->label('Select Item')
                            ->options(function (callable $get) {
                                $type = $get('documentable_type');
                                if (!$type || !array_key_exists($type, self::ALLOWED_DOCUMENTABLE_TYPES)) {
                                    return [];
                                }
                                // Validate that the class exists and is a valid model
                                if (!class_exists($type) || !is_subclass_of($type, \Illuminate\Database\Eloquent\Model::class)) {
                                    return [];
                                }
                                return $type::pluck('name', 'id')->toArray();
                            })
                            ->searchable()
                            ->hidden(fn (callable $get) => !$get('documentable_type')),
                    ])->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Document Name')
                    ->limit(50),
                BadgeColumn::make('document_type')
                    ->searchable()
                    ->sortable()
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),
                TextColumn::make('version')
                    ->sortable()
                    ->label('Version'),
                BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'draft',
                        'success' => 'active',
                        'warning' => 'archived',
                        'danger' => 'obsolete',
                    ])
                    ->label('Status'),
                BadgeColumn::make('approval_status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->label('Approval'),
                TextColumn::make('compliance_standard')
                    ->searchable()
                    ->sortable()
                    ->label('Compliance')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->label('Expires')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->color(fn ($record) => $record->isExpired() ? 'danger' : ($record->isExpiringSoon() ? 'warning' : null)),
                TextColumn::make('review_date')
                    ->date()
                    ->sortable()
                    ->label('Review Date')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('documentable_type')
                    ->label('Attached To')
                    ->formatStateUsing(fn ($state) => $state ? class_basename($state) : 'None')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Created')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('document_type')
                    ->options([
                        'manual' => 'Manual',
                        'service_record' => 'Service Record',
                        'compliance' => 'Compliance Document',
                        'procedure' => 'Procedure',
                        'checklist' => 'Checklist',
                        'report' => 'Report',
                        'certificate' => 'Certificate',
                        'other' => 'Other',
                    ])
                    ->label('Type'),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'archived' => 'Archived',
                        'obsolete' => 'Obsolete',
                    ])
                    ->label('Status'),
                SelectFilter::make('approval_status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->label('Approval Status'),
                Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->expired())
                    ->label('Expired'),
                Filter::make('expiring_soon')
                    ->query(fn (Builder $query): Builder => $query->expiringSoon(30))
                    ->label('Expiring Soon (30 days)'),
                Filter::make('due_for_review')
                    ->query(fn (Builder $query): Builder => $query->dueForReview())
                    ->label('Due for Review'),
            ])
            ->recordActions([
                ViewAction::make(),
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
            'index' => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'view' => ViewDocument::route('/{record}'),
            'edit' => EditDocument::route('/{record}/edit'),
        ];
    }
}
