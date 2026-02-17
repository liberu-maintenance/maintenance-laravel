<?php

namespace App\Filament\App\Resources\VendorContracts;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\VendorContracts\Pages\ListVendorContracts;
use App\Filament\App\Resources\VendorContracts\Pages\CreateVendorContract;
use App\Filament\App\Resources\VendorContracts\Pages\EditVendorContract;
use Filament\Forms;
use Filament\Tables;
use App\Models\VendorContract;
use App\Models\Company;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class VendorContractResource extends Resource
{
    protected static ?string $model = VendorContract::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Vendor Management';

    protected static ?string $navigationLabel = 'Contracts';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Contract Information')
                    ->schema([
                        Select::make('vendor_id')
                            ->label('Vendor')
                            ->relationship(
                                'vendor',
                                'name',
                                fn (Builder $query) => $query->whereIn('type', ['vendor', 'supplier', 'both'])
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('contract_number')
                            ->label('Contract Number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255),
                        Select::make('contract_type')
                            ->label('Contract Type')
                            ->options([
                                'service' => 'Service',
                                'maintenance' => 'Maintenance',
                                'supply' => 'Supply',
                                'other' => 'Other',
                            ])
                            ->default('service')
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'expired' => 'Expired',
                                'terminated' => 'Terminated',
                                'renewed' => 'Renewed',
                            ])
                            ->default('draft')
                            ->required(),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3),
                    ])->columns(2),

                Section::make('Financial Terms')
                    ->schema([
                        TextInput::make('contract_value')
                            ->label('Contract Value')
                            ->numeric()
                            ->prefix('$')
                            ->maxValue(9999999.99),
                        Select::make('currency')
                            ->label('Currency')
                            ->options([
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                                'GBP' => 'GBP',
                            ])
                            ->default('USD')
                            ->required(),
                        Select::make('payment_frequency')
                            ->label('Payment Frequency')
                            ->options([
                                'one_time' => 'One Time',
                                'monthly' => 'Monthly',
                                'quarterly' => 'Quarterly',
                                'annually' => 'Annually',
                            ]),
                        Textarea::make('terms_and_conditions')
                            ->label('Terms and Conditions')
                            ->rows(4),
                    ])->columns(2),

                Section::make('Contract Period')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->required()
                            ->after('start_date'),
                        DatePicker::make('renewal_date')
                            ->label('Renewal Date'),
                        TextInput::make('renewal_period_months')
                            ->label('Renewal Period (Months)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(120),
                        Toggle::make('auto_renewal')
                            ->label('Auto Renewal')
                            ->default(false),
                    ])->columns(3),

                Section::make('Additional Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(4),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contract_number')
                    ->label('Contract #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contract_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'service' => 'primary',
                        'maintenance' => 'success',
                        'supply' => 'info',
                        'other' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'gray',
                        'expired' => 'warning',
                        'terminated' => 'danger',
                        'renewed' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('contract_value')
                    ->label('Value')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('payment_frequency')
                    ->label('Payment')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'terminated' => 'Terminated',
                        'renewed' => 'Renewed',
                    ]),
                SelectFilter::make('contract_type')
                    ->options([
                        'service' => 'Service',
                        'maintenance' => 'Maintenance',
                        'supply' => 'Supply',
                        'other' => 'Other',
                    ])
                    ->label('Type'),
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
            ->defaultSort('start_date', 'desc');
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
            'index' => ListVendorContracts::route('/'),
            'create' => CreateVendorContract::route('/create'),
            'edit' => EditVendorContract::route('/{record}/edit'),
        ];
    }
}
