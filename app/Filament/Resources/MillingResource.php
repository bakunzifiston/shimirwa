<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MillingResource\Pages;
use App\Models\Milling;
use App\Models\Roasting;
use App\Models\Sorting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class MillingResource extends Resource
{
    protected static ?string $model = Milling::class;
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\DatePicker::make('date')->required(),

            Forms\Components\Repeater::make('items')
                ->label('Ingredients Used')
                ->schema([

                    Forms\Components\Select::make('type')
                        ->label('Ingredient')
                        ->options([
                            'soy'     => 'Soy',
                            'sorghum' => 'Sorghum',
                            'wheat'   => 'Wheat',
                            'maize'   => 'Maize',
                        ])
                        ->required()
                        ->reactive(),

                    Forms\Components\Select::make('stock_id')
                        ->label('Batch')
                        ->required()
                        ->options(function (callable $get) {
                            $type = $get('type');
                            if (!$type) return [];

                            return match ($type) {
                                'soy', 'maize' =>
                                    Roasting::where('quantity_in', '>', 0)
                                        ->get()
                                        ->mapWithKeys(fn ($s) =>
                                            [$s->id => "{$s->batch} ({$s->quantity_in} kg left)"]
                                        ),

                                'sorghum', 'wheat' =>
                                    Sorting::where('quantity_in', '>', 0)
                                        ->get()
                                        ->mapWithKeys(fn ($s) =>
                                            [$s->id => $s->rawMaterialStock
                                                ? "{$s->rawMaterialStock->item} - {$s->rawMaterialStock->batch_number} ({$s->quantity_in} kg left)"
                                                : 'Unknown']
                                        ),

                                default => [],
                            };
                        })
                        ->reactive()
                        ->searchable(),

                    Forms\Components\TextInput::make('quantity')
                        ->numeric()
                        ->required()
                        ->default(0)
                        ->reactive(),

                ])
                ->minItems(1)
                ->columns(3)
                // Pass current loss value so output_flour is correct immediately
                ->afterStateUpdated(fn ($state, callable $set, callable $get) =>
                    self::computeTotalsRepeater($state, $set, (float) $get('loss'))
                ),

            Forms\Components\TextInput::make('loss')
                ->numeric()
                ->default(0)
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    self::computeTotalsRepeater($get('items'), $set, (float) $state);
                }),

            // total_mixed_quantity — display only, saved via model event
            Forms\Components\TextInput::make('total_mixed_quantity')
                ->label('Total Mixed (kg)')
                ->disabled()
                ->dehydrated()
                ->helperText('Sum of all ingredient quantities'),

            // output_flour — display only, saved via model event
            Forms\Components\TextInput::make('output_flour')
                ->label('Output Flour (kg)')
                ->disabled()
                ->dehydrated()
                ->helperText('Total Mixed − Loss'),

            Forms\Components\TextInput::make('batch_number')
                ->required(),

            Forms\Components\Select::make('employee_id')
                ->relationship('employee', 'full_name')
                ->searchable()
                ->preload()
                ->required(),
        ]);
    }

    private static function computeTotalsRepeater($items, callable $set, float $loss = 0): void
    {
        $total = 0;

        if (is_array($items)) {
            foreach ($items as $row) {
                $total += (float) ($row['quantity'] ?? 0);
            }
        }

        $set('total_mixed_quantity', $total);
        $set('output_flour', max($total - $loss, 0));
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('date')->date()->sortable(),

            TextColumn::make('items')
                ->label('Ingredients')
                ->getStateUsing(fn ($record) => count($record->items ?? [])),

            TextColumn::make('total_mixed_quantity')->numeric(),
            TextColumn::make('output_flour')->numeric(),
            TextColumn::make('loss')->numeric(),

            TextColumn::make('batch_number')->searchable(),
            TextColumn::make('employee.full_name')->label('Employee'),

            TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])
->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListMillings::route('/'),
            'create' => Pages\CreateMilling::route('/create'),
            'view' => Pages\ViewMilling::route('/{record}'),
            'edit' => Pages\EditMilling::route('/{record}/edit'),
        ];
    }
}
