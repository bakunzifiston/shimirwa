<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Sale;
use App\Models\Emballage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?int $navigationSort = 9;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Sale Information')
                ->columns(2)
                ->schema([
                    Forms\Components\DatePicker::make('date')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y'),

                    Forms\Components\TextInput::make('item')
                        ->label('Product Name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('client_id')
                        ->relationship('client', 'full_name')
                        ->label('Client')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('employee_id')
                        ->relationship('employee', 'full_name')
                        ->label('Sales Employee')
                        ->searchable()
                        ->preload()
                        ->required(),
                ]),

            Forms\Components\Section::make('Batches Sold')
                ->schema([
                    Forms\Components\Repeater::make('batches')
                        ->label('')
                        ->schema([
                            Forms\Components\Select::make('emballage_id')
                                ->label('Packaging Batch')
                                ->options(fn () => Emballage::with('milling')
                                    ->where('item', '>', 0)
                                    ->get()
                                    ->mapWithKeys(fn ($emb) => [
                                        $emb->id => "Batch: " . ($emb->packaging_batch_id ?? '—')
                                            . " | Type: " . strtoupper($emb->packaging_type ?? '—')
                                            . " | Stock: {$emb->item} units"
                                            . " | Price: " . number_format($emb->unit_price ?? 0) . " RWF",
                                    ])
                                    ->toArray())
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $emb = Emballage::find($state);
                                        if ($emb) {
                                            $set('unit_price', $emb->unit_price);
                                            $set('line_total', $emb->unit_price);
                                        }
                                    }
                                }),

                            Forms\Components\TextInput::make('quantity')
                                ->label('Quantity')
                                ->numeric()
                                ->minValue(1)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $set('line_total', (float) $get('unit_price') * (int) $state);
                                }),

                            Forms\Components\TextInput::make('unit_price')
                                ->label('Unit Price (RWF)')
                                ->numeric()
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $set('line_total', (float) $state * (int) ($get('quantity') ?? 1));
                                }),

                            Forms\Components\TextInput::make('line_total')
                                ->label('Line Total (RWF)')
                                ->numeric()
                                ->readOnly()
                                ->dehydrated(),
                        ])
                        ->columns(4)
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('total_price', collect($state)->sum(fn ($b) => $b['line_total'] ?? 0));
                        })
                        ->required()
                        ->dehydrated(),
                ]),

            Forms\Components\Section::make('Totals & Returns')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('total_price')
                        ->label('Total Price (RWF)')
                        ->numeric()
                        ->readOnly()
                        ->dehydrated()
                        ->required(),

                    Forms\Components\TextInput::make('returned')
                        ->label('Returned Quantity')
                        ->numeric()
                        ->default(0),

                    Forms\Components\TextInput::make('reason')
                        ->label('Reason for Return')
                        ->maxLength(255)
                        ->columnSpan(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('date')->date()->sortable(),
            Tables\Columns\TextColumn::make('client.full_name')->label('Client')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('item')->label('Product')->searchable(),
            Tables\Columns\TextColumn::make('total_price')
                ->label('Total (RWF)')
                ->numeric()
                ->sortable()
                ->money('RWF'),
            Tables\Columns\TextColumn::make('employee.full_name')->label('Employee')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('returned')
                ->numeric()
                ->sortable()
                ->toggleable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListSales::route('/'),
        ];
    }
}
