<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SortingResource\Pages;
use App\Models\Sorting;
use App\Models\RawMaterialStock;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\BelongsToSelect;
use Filament\Tables\Columns\TextColumn;

class SortingResource extends Resource
{
    protected static ?string $model = Sorting::class;
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Sorting Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        BelongsToSelect::make('raw_material_stock_id')
                            ->label('Source Batch')
                            ->relationship(
                                'rawMaterialStock',
                                'batch_number',
                                fn ($query) => $query->where('quantity_in', '>', 0),
                            )
                            ->getOptionLabelFromRecordUsing(function (RawMaterialStock $record) {
                                return "{$record->item} — {$record->batch_number} (Available: {$record->quantity_in} kg)";
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('quantity_in')
                            ->label('Quantity to Sort (kg)')
                            ->required()
                            ->numeric()
                            ->minValue(0.01),

                        Forms\Components\TextInput::make('loss')
                            ->label('Loss / Waste (kg)')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Quantity that cannot be used after sorting'),

                        BelongsToSelect::make('employee_id')
                            ->relationship('employee', 'full_name')
                            ->label('Responsible Employee')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('rawMaterialStock.item')
                    ->label('Item')
                    ->searchable(),
                TextColumn::make('rawMaterialStock.batch_number')
                    ->label('Batch')
                    ->searchable(),
                TextColumn::make('quantity_in')
                    ->label('Qty In (kg)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('loss')
                    ->label('Loss (kg)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quantity_out')
                    ->label('Available (kg)')
                    ->getStateUsing(fn ($record) => max((float) $record->quantity_in - (float) ($record->loss ?? 0), 0))
                    ->numeric()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make()->slideOver(),
                Tables\Actions\EditAction::make()->slideOver(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSortings::route('/'),
        ];
    }
}
