<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoastingResource\Pages;
use App\Models\Roasting;
use App\Models\RawMaterialStock;
use App\Models\Sorting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\BelongsToSelect;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;

class RoastingResource extends Resource
{
    protected static ?string $model = Roasting::class;
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->required(),

                // Step 1: Choose source type
                Select::make('source_type')
                    ->label('Source')
                    ->options([
                        'raw' => 'Raw Material Stock',
                        'sorting' => 'Sorting Stock',
                    ])
                    ->reactive()
                    ->required(),

                // Step 2a: Raw Material Stock batch selector
                BelongsToSelect::make('raw_material_stock_id')
                    ->label('Batch (Raw Material)')
                    ->relationship('rawMaterialStock', 'batch_number')
                    ->getOptionLabelFromRecordUsing(function (RawMaterialStock $record) {
                        return "{$record->item} - {$record->batch_number} (Available: {$record->quantity_in} kg)";
                    })
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get) => $get('source_type') === 'raw')
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set) {
                        $stock = RawMaterialStock::find($state);
                        $set('batch', $stock?->batch_number ?? '');
                    }),

                // Step 2b: Sorting batch selector
                BelongsToSelect::make('sorting_id')
                    ->label('Batch (Sorting)')
                    ->relationship('sorting', 'id')
                    ->getOptionLabelFromRecordUsing(function (Sorting $record) {
                        $stock = $record->rawMaterialStock;
                        return $stock
                            ? "{$stock->item} - Batch {$stock->batch_number} (Available: {$record->quantity_in} kg)"
                            : "Unknown Batch (ID: {$record->id})";
                    })
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get) => $get('source_type') === 'sorting')
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set) {
                        $sorting = Sorting::find($state);
                        $set('batch', $sorting?->rawMaterialStock?->batch_number ?? '');
                    }),

                Forms\Components\TextInput::make('quantity_in')
                    ->label('Quantity In')
                    ->required()
                    ->numeric(),

                Forms\Components\TextInput::make('loss')
                    ->required()
                    ->numeric()
                    ->default(0),

                // Auto-filled Batch Number (Read-Only)
                Forms\Components\TextInput::make('batch')
                    ->label('Roasting Batch Number')
                    ->required()
                    ->maxLength(255),
                 // prevent manual changes

                BelongsToSelect::make('chef_id')
                    ->relationship('chef', 'full_name')
                    ->searchable()
                    ->required(),

                BelongsToSelect::make('supervisor_id')
                    ->relationship('supervisor', 'full_name')
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
       
   
                TextColumn::make('quantity_in')
                    ->label('Quantity In')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('loss')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('batch')
                    ->label('Roasting Batch')
                    ->searchable(),
                TextColumn::make('chef.full_name')
                    ->label('Chef')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('supervisor.full_name')
                    ->label('Supervisor')
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
            'index' => Pages\ListRoastings::route('/'),
            'create' => Pages\CreateRoasting::route('/create'),
            'view' => Pages\ViewRoasting::route('/{record}'),
            'edit' => Pages\EditRoasting::route('/{record}/edit'),
        ];
    }
}
