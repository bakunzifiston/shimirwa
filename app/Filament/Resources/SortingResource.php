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
                Forms\Components\DatePicker::make('date')
                    ->required(),

                // Select batch from raw_material_stocks
                BelongsToSelect::make('raw_material_stock_id')
                ->label('Batch (Item - Batch Number)')
                ->relationship('rawMaterialStock', 'batch_number')
                ->getOptionLabelFromRecordUsing(function (\App\Models\RawMaterialStock $record) {
                    return "{$record->item} - {$record->batch_number} (Available: {$record->quantity_in} kg)";
                })
                ->searchable()
                ->required(),
                Forms\Components\TextInput::make('quantity_in')
                    ->label('Quantity ')
                    ->required()
                    ->numeric(),

                Forms\Components\TextInput::make('loss')
                    ->required()
                    ->numeric()
                    ->default(0),

                BelongsToSelect::make('employee_id')
                    ->relationship('employee', 'full_name')
                    ->searchable()
                    ->required(),
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
                    ->label('Batch Number')
                    ->searchable(),

                TextColumn::make('quantity_in')
                    ->label('Quantity ')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('loss')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'create' => Pages\CreateSorting::route('/create'),
            'view' => Pages\ViewSorting::route('/{record}'),
            'edit' => Pages\EditSorting::route('/{record}/edit'),
        ];
    }
}
