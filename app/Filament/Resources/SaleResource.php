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
            Forms\Components\DatePicker::make('date')->required(),

            Forms\Components\TextInput::make('item')
                ->label('Product Name')
                ->required()
                ->maxLength(255),

            Forms\Components\Repeater::make('batches')
                ->label('Batches')
                ->schema([
                    Forms\Components\Select::make('emballage_id')
                        ->label('Packaging Batch')
                        ->options(fn() => Emballage::with('milling')
                            ->where('item', '>', 0)
                            ->get()
                            ->mapWithKeys(fn ($emb) => [
                                $emb->id => "Batch: " . ($emb->packaging_batch_id ?? '—')
                                    . " | Type: " . strtoupper($emb->packaging_type ?? '—')
                                    . " | Stock: {$emb->item} units"
                                    . " | Price: " . number_format($emb->unit_price ?? 0) . " RWF"
                            ])
                            ->toArray())
                        ->searchable()
                        ->required()
                        ->reactive()
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
                        ->numeric()
                        ->minValue(1)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $price = (float) $get('unit_price');
                            $set('line_total', $price * (int) $state);
                        }),

                    Forms\Components\TextInput::make('unit_price')
                        ->numeric()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $quantity = (int) $get('quantity') ?? 1;
                            $set('line_total', $state * $quantity);
                        }),

                    Forms\Components\TextInput::make('line_total')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(), // save line_total
                ])
                ->columns(4)
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $total = collect($state)->sum(fn ($b) => $b['line_total'] ?? 0);
                    $set('total_price', $total);
                })
                ->required()
                ->columnSpanFull()
                ->dehydrated(), // save repeater as JSON

            Forms\Components\TextInput::make('total_price')
                ->numeric()
                ->disabled()
                ->dehydrated()
                ->required(),

            Forms\Components\Select::make('client_id')
                ->relationship('client', 'full_name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('employee_id')
                ->relationship('employee', 'full_name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\TextInput::make('returned')
                ->label('Returned Quantity')
                ->numeric()
                ->default(0),

            Forms\Components\TextInput::make('reason')
                ->label('Reason for Return')
                ->maxLength(255)
                ->nullable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('date')->date()->sortable(),
               Tables\Columns\TextColumn::make('client.full_name')->label('Client')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('item')->label('Product')->searchable(),
            Tables\Columns\TextColumn::make('total_price')->numeric()->sortable(),



         
            Tables\Columns\TextColumn::make('employee.full_name')->label('Employee')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('returned')->numeric()->sortable(),
            Tables\Columns\TextColumn::make('reason')->searchable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
        ])
        ->defaultSort('created_at', 'desc')
        ->filters([])
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'view' => Pages\ViewSale::route('/{record}'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
