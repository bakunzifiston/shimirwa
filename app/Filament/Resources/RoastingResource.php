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
                Forms\Components\Section::make('Roasting Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Select::make('source_type')
                            ->label('Source Type')
                            ->options([
                                'raw'     => 'Raw Material Stock',
                                'sorting' => 'Sorting Stock',
                            ])
                            ->live()
                            ->dehydrated(false)
                            ->required(),

                        BelongsToSelect::make('raw_material_stock_id')
                            ->label('Batch (Raw Material)')
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
                            ->visible(fn ($get) => $get('source_type') === 'raw')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                $stock = RawMaterialStock::find($state);
                                $set('batch', $stock?->batch_number ?? '');
                            }),

                        BelongsToSelect::make('sorting_id')
                            ->label('Batch (Sorting)')
                            ->relationship(
                                'sorting',
                                'id',
                                fn ($query) => $query->where('quantity_in', '>', 0),
                            )
                            ->getOptionLabelFromRecordUsing(function (Sorting $record) {
                                $stock = $record->rawMaterialStock;
                                return $stock
                                    ? "{$stock->item} — Batch {$stock->batch_number} (Available: {$record->quantity_in} kg)"
                                    : "Sorting Batch #{$record->id}";
                            })
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('source_type') === 'sorting')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                $sorting = Sorting::find($state);
                                $set('batch', $sorting?->rawMaterialStock?->batch_number ?? '');
                            }),

                        Forms\Components\TextInput::make('quantity_in')
                            ->label('Quantity In (kg)')
                            ->required()
                            ->numeric()
                            ->minValue(0.01),

                        Forms\Components\TextInput::make('loss')
                            ->label('Loss / Waste (kg)')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\TextInput::make('batch')
                            ->label('Roasting Batch Number')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Auto-filled when you select a source batch'),
                    ]),

                Forms\Components\Section::make('Staff')
                    ->columns(2)
                    ->schema([
                        BelongsToSelect::make('chef_id')
                            ->relationship('chef', 'full_name')
                            ->label('Chef')
                            ->searchable()
                            ->preload()
                            ->required(),

                        BelongsToSelect::make('supervisor_id')
                            ->relationship('supervisor', 'full_name')
                            ->label('Supervisor')
                            ->searchable()
                            ->preload()
                            ->required(),
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
                TextColumn::make('batch')
                    ->label('Roasting Batch')
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
            'index' => Pages\ListRoastings::route('/'),
        ];
    }
}
