<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RawMaterialStockResource\Pages;
use App\Models\RawMaterialStock;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\BelongsToSelect;
use Filament\Tables\Columns\TextColumn;

class RawMaterialStockResource extends Resource
{
    protected static ?string $model = RawMaterialStock::class;
    protected static ?string $navigationLabel = 'Reception of Materials';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-tag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        BelongsToSelect::make('client_id')
                            ->relationship('client', 'full_name')
                            ->label('Supplier')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'Raw Material'    => 'Raw Material',
                                'Packaging Staff' => 'Packaging Staff',
                                'Other'           => 'Other',
                            ])
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('item')
                            ->label('Item')
                            ->options(function (callable $get) {
                                return match ($get('type')) {
                                    'Raw Material' => [
                                        'Maize'   => 'Maize',
                                        'Soy'     => 'Soy',
                                        'Sorghum' => 'Sorghum',
                                        'Wheat'   => 'Wheat',
                                    ],
                                    'Packaging Staff' => [
                                        '5kg'  => '5kg',
                                        '1kg'  => '1kg',
                                        'Box'  => 'Box',
                                        'Sack' => 'Sack',
                                    ],
                                    default => [],
                                };
                            })
                            ->required()
                            ->searchable()
                            ->live()
                            ->hidden(fn (callable $get) => $get('type') === 'Other'),

                        Forms\Components\TextInput::make('item')
                            ->label('Item (custom)')
                            ->placeholder('Enter item name')
                            ->required(fn (callable $get) => $get('type') === 'Other')
                            ->hidden(fn (callable $get) => $get('type') !== 'Other'),
                    ]),

                Forms\Components\Section::make('Quantities')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('received')
                            ->label('Received Quantity (kg)')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $set('quantity_in', max(0, (float) $state - (float) ($get('rejected') ?? 0)));
                            }),

                        Forms\Components\TextInput::make('rejected')
                            ->label('Rejected Quantity (kg)')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $set('quantity_in', max(0, (float) ($get('received') ?? 0) - (float) $state));
                            }),

                        Forms\Components\TextInput::make('quantity_in')
                            ->label('Net Quantity In (kg)')
                            ->numeric()
                            ->readOnly()
                            ->default(0)
                            ->helperText('Auto-calculated: Received − Rejected')
                            ->columnSpan(2),
                    ]),

                Forms\Components\Section::make('Tracking')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('batch_number')
                            ->label('Batch Number')
                            ->required()
                            ->maxLength(255),

                        BelongsToSelect::make('employee_id')
                            ->relationship('employee', 'full_name')
                            ->label('Responsible Employee')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Textarea::make('comment')
                            ->label('Comment / Notes')
                            ->maxLength(500)
                            ->rows(2)
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
                TextColumn::make('client.full_name')
                    ->label('Supplier')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('item')
                    ->searchable(),
                TextColumn::make('received')
                    ->label('Received (kg)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rejected')
                    ->label('Rejected (kg)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quantity_in')
                    ->label('Net In (kg)')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                TextColumn::make('batch_number')
                    ->label('Batch')
                    ->searchable(),
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
            'index' => Pages\ListRawMaterialStocks::route('/'),
        ];
    }
}
