<?php

namespace App\Filament\App\Resources\Vendors;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\Vendors\Pages\ListVendors;
use App\Filament\App\Resources\Vendors\Pages\CreateVendor;
use App\Filament\App\Resources\Vendors\Pages\EditVendor;
use Filament\Forms;
use Filament\Tables;
use App\Models\Company;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;

class VendorResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string | \UnitEnum | null $navigationGroup = 'Vendor Management';

    protected static ?string $navigationLabel = 'Vendors';

    protected static ?string $modelLabel = 'Vendor';

    protected static ?string $pluralModelLabel = 'Vendors';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('type', ['vendor', 'supplier', 'both']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Vendor Name')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'vendor' => 'Vendor',
                                'supplier' => 'Supplier',
                                'both' => 'Both (Customer & Vendor)',
                            ])
                            ->default('vendor')
                            ->required(),
                        TextInput::make('contact_person')
                            ->label('Contact Person')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(2),

                Section::make('Address')
                    ->schema([
                        TextInput::make('address')
                            ->label('Street Address')
                            ->maxLength(255),
                        TextInput::make('city')
                            ->label('City')
                            ->maxLength(255),
                        TextInput::make('state')
                            ->label('State')
                            ->maxLength(255),
                        TextInput::make('zip')
                            ->label('ZIP')
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Additional Information')
                    ->schema([
                        TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('industry')
                            ->label('Industry/Specialization')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3),
                        Textarea::make('payment_terms')
                            ->label('Payment Terms')
                            ->rows(3),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Vendor Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vendor' => 'primary',
                        'supplier' => 'info',
                        'both' => 'warning',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_person')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),
                TextColumn::make('vendorContracts')
                    ->label('Active Contracts')
                    ->counts([
                        'vendorContracts' => fn (Builder $query) => $query->where('status', 'active'),
                    ])
                    ->sortable(),
                TextColumn::make('vendorPerformanceEvaluations')
                    ->label('Avg Rating')
                    ->getStateUsing(fn (Company $record): string => 
                        number_format($record->getAveragePerformanceRating(), 2)
                    )
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withAvg('vendorPerformanceEvaluations', 'overall_rating')
                            ->orderBy('vendor_performance_evaluations_avg_overall_rating', $direction);
                    })
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        (float) $state >= 4.0 => 'success',
                        (float) $state >= 3.0 => 'warning',
                        (float) $state > 0 => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'vendor' => 'Vendor',
                        'supplier' => 'Supplier',
                        'both' => 'Both',
                    ]),
                SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->label('Status'),
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
            'index' => ListVendors::route('/'),
            'create' => CreateVendor::route('/create'),
            'edit' => EditVendor::route('/{record}/edit'),
        ];
    }
}
