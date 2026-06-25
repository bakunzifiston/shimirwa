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
            Forms\Components\Section::make('Milling Details')
                ->columns(2)
                ->schema([
                    Forms\Components\DatePicker::make('date')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y'),

                    Forms\Components\TextInput::make('batch_number')
                        ->label('Milling Batch Number')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('employee_id')
                        ->relationship('employee', 'full_name')
                        ->label('Responsible Employee')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan(2),
                ]),

            Forms\Components\Section::make('Ingredients Used')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->label('')
                        ->schema([
                            Forms\Components\Select::make('type')
                                ->label('Ingredient')
                                ->options([
                                    'soy'     => 'Soy (Roasted)',
                                    'sorghum' => 'Sorghum (Sorted)',
                                    'wheat'   => 'Wheat (Sorted)',
                                    'maize'   => 'Maize (Roasted)',
                                ])
                                ->required()
                                ->live(),

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
                                                    [$s->id => "{$s->batch} ({$s->quantity_in} kg available)"]
                                                ),

                                        'sorghum', 'wheat' =>
                                            Sorting::where('quantity_in', '>', 0)
                                                ->get()
                                                ->mapWithKeys(fn ($s) =>
                                                    [$s->id => $s->rawMaterialStock
                                                        ? "{$s->rawMaterialStock->item} — {$s->rawMaterialStock->batch_number} ({$s->quantity_in} kg available)"
                                                        : 'Unknown']
                                                ),

                                        default => [],
                                    };
                                })
                                ->live()
                                ->searchable(),

                            Forms\Components\TextInput::make('quantity')
                                ->label('Quantity (kg)')
                                ->numeric()
                                ->required()
                                ->default(0)
                                ->live(onBlur: true),
                        ])
                        ->minItems(1)
                        ->columns(3)
                        ->afterStateUpdated(fn ($state, callable $set, callable $get) =>
                            self::computeTotalsRepeater($state, $set, (float) $get('loss'))
                        ),
                ]),

            Forms\Components\Section::make('Output')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('loss')
                        ->label('Loss / Waste (kg)')
                        ->numeric()
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            self::computeTotalsRepeater($get('items'), $set, (float) $state);
                        }),

                    Forms\Components\TextInput::make('total_mixed_quantity')
                        ->label('Total Mixed (kg)')
                        ->readOnly()
                        ->dehydrated()
                        ->helperText('Sum of all ingredient quantities'),

                    Forms\Components\TextInput::make('output_flour')
                        ->label('Output Flour (kg)')
                        ->readOnly()
                        ->dehydrated()
                        ->helperText('Total Mixed − Loss'),
                ]),
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
            TextColumn::make('batch_number')->label('Batch')->searchable(),
            TextColumn::make('items')
                ->label('Ingredients')
                ->getStateUsing(fn ($record) => count($record->items ?? [])),
            TextColumn::make('total_mixed_quantity')
                ->label('Total Mixed (kg)')
                ->numeric(),
            TextColumn::make('loss')
                ->label('Loss (kg)')
                ->numeric(),
            TextColumn::make('output_flour')
                ->label('Output Flour (kg)')
                ->numeric()
                ->badge()
                ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
            TextColumn::make('employee.full_name')->label('Employee')->searchable(),
            TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListMillings::route('/'),
        ];
    }
}
