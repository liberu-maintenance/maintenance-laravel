<?php

namespace App\Filament\App\Resources\VendorPerformanceEvaluations;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\VendorPerformanceEvaluations\Pages\ListVendorPerformanceEvaluations;
use App\Filament\App\Resources\VendorPerformanceEvaluations\Pages\CreateVendorPerformanceEvaluation;
use App\Filament\App\Resources\VendorPerformanceEvaluations\Pages\EditVendorPerformanceEvaluation;
use Filament\Forms;
use Filament\Tables;
use App\Models\VendorPerformanceEvaluation;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\RawJs;

class VendorPerformanceEvaluationResource extends Resource
{
    protected static ?string $model = VendorPerformanceEvaluation::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-star';

    protected static string | \UnitEnum | null $navigationGroup = 'Vendor Management';

    protected static ?string $navigationLabel = 'Performance Evaluations';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Evaluation Details')
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
                        Select::make('vendor_contract_id')
                            ->label('Related Contract')
                            ->relationship('contract', 'title')
                            ->searchable()
                            ->preload(),
                        Select::make('work_order_id')
                            ->label('Related Work Order')
                            ->relationship('workOrder', 'title')
                            ->searchable()
                            ->preload(),
                        DatePicker::make('evaluation_date')
                            ->label('Evaluation Date')
                            ->default(now())
                            ->required(),
                        Select::make('evaluated_by')
                            ->label('Evaluated By')
                            ->relationship('evaluator', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),

                Section::make('Performance Ratings')
                    ->description('Rate each category from 1 (Poor) to 5 (Excellent)')
                    ->schema([
                        Select::make('quality_rating')
                            ->label('Quality of Work')
                            ->options([
                                1 => '⭐ 1 - Poor',
                                2 => '⭐⭐ 2 - Fair',
                                3 => '⭐⭐⭐ 3 - Good',
                                4 => '⭐⭐⭐⭐ 4 - Very Good',
                                5 => '⭐⭐⭐⭐⭐ 5 - Excellent',
                            ])
                            ->default(0)
                            ->required(),
                        Select::make('timeliness_rating')
                            ->label('Timeliness')
                            ->options([
                                1 => '⭐ 1 - Poor',
                                2 => '⭐⭐ 2 - Fair',
                                3 => '⭐⭐⭐ 3 - Good',
                                4 => '⭐⭐⭐⭐ 4 - Very Good',
                                5 => '⭐⭐⭐⭐⭐ 5 - Excellent',
                            ])
                            ->default(0)
                            ->required(),
                        Select::make('communication_rating')
                            ->label('Communication')
                            ->options([
                                1 => '⭐ 1 - Poor',
                                2 => '⭐⭐ 2 - Fair',
                                3 => '⭐⭐⭐ 3 - Good',
                                4 => '⭐⭐⭐⭐ 4 - Very Good',
                                5 => '⭐⭐⭐⭐⭐ 5 - Excellent',
                            ])
                            ->default(0)
                            ->required(),
                        Select::make('cost_effectiveness_rating')
                            ->label('Cost Effectiveness')
                            ->options([
                                1 => '⭐ 1 - Poor',
                                2 => '⭐⭐ 2 - Fair',
                                3 => '⭐⭐⭐ 3 - Good',
                                4 => '⭐⭐⭐⭐ 4 - Very Good',
                                5 => '⭐⭐⭐⭐⭐ 5 - Excellent',
                            ])
                            ->default(0)
                            ->required(),
                        Select::make('professionalism_rating')
                            ->label('Professionalism')
                            ->options([
                                1 => '⭐ 1 - Poor',
                                2 => '⭐⭐ 2 - Fair',
                                3 => '⭐⭐⭐ 3 - Good',
                                4 => '⭐⭐⭐⭐ 4 - Very Good',
                                5 => '⭐⭐⭐⭐⭐ 5 - Excellent',
                            ])
                            ->default(0)
                            ->required(),
                    ])->columns(2),

                Section::make('Feedback')
                    ->schema([
                        Textarea::make('strengths')
                            ->label('Strengths')
                            ->rows(3),
                        Textarea::make('areas_for_improvement')
                            ->label('Areas for Improvement')
                            ->rows(3),
                        Textarea::make('comments')
                            ->label('Additional Comments')
                            ->rows(3),
                        Toggle::make('would_recommend')
                            ->label('Would Recommend This Vendor')
                            ->default(true),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('evaluation_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('overall_rating')
                    ->label('Overall Rating')
                    ->numeric(decimalPlaces: 2)
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state >= 4.0 => 'success',
                        $state >= 3.0 => 'warning',
                        $state > 0 => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('quality_rating')
                    ->label('Quality')
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        $state > 0 => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('timeliness_rating')
                    ->label('Timeliness')
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        $state > 0 => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('communication_rating')
                    ->label('Communication')
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        $state > 0 => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('evaluator.name')
                    ->label('Evaluated By')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('contract.contract_number')
                    ->label('Contract')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('workOrder.title')
                    ->label('Work Order')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('vendor_id')
                    ->label('Vendor')
                    ->relationship('vendor', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('overall_rating')
                    ->options([
                        '4+' => 'Excellent (4+)',
                        '3-4' => 'Good (3-4)',
                        '0-3' => 'Needs Improvement (<3)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            '4+' => $query->where('overall_rating', '>=', 4.0),
                            '3-4' => $query->whereBetween('overall_rating', [3.0, 4.0]),
                            '0-3' => $query->where('overall_rating', '<', 3.0),
                            default => $query,
                        };
                    }),
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
            ->defaultSort('evaluation_date', 'desc');
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
            'index' => ListVendorPerformanceEvaluations::route('/'),
            'create' => CreateVendorPerformanceEvaluation::route('/create'),
            'edit' => EditVendorPerformanceEvaluation::route('/{record}/edit'),
        ];
    }
}
