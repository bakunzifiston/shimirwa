<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmballageResource\Pages;
use App\Models\Emballage;
use App\Models\Milling;
use App\Models\RawMaterialStock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmballageResource extends Resource
{
    protected static ?string $model = Emballage::class;
    protected static ?int $navigationSort = 8;
    protected static ?string $navigationLabel = 'Packaging';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Packaging Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Forms\Components\TextInput::make('packaging_batch_id')
                            ->label('Packaging Batch ID')
                            ->required(),

                        Forms\Components\Select::make('packaging_type')
                            ->label('Packaging Type')
                            ->options([
                                'box'  => 'Box (1 box = 12 × 1kg = 12kg)',
                                '1kg'  => '1kg Package',
                                '5kg'  => '5kg Package',
                                'sack' => 'Sack (enter weight manually)',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state === 'sack') {
                                    $set('quantity', null);
                                } else {
                                    $set('quantity', (float) ($get('item') ?? 0) * Emballage::packagingKg($state));
                                }
                                $set('envelope_stock_id', null);
                            }),

                        Forms\Components\Select::make('milling_id')
                            ->label('Milling Batch (flour source)')
                            ->options(
                                Milling::all()->mapWithKeys(fn ($m) => [
                                    $m->id => "{$m->batch_number} — Output: {$m->output_flour}kg available",
                                ])
                            )
                            ->searchable()
                            ->live(),
                    ]),

                Forms\Components\Section::make('Materials')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('raw_material_stock_id')
                            ->label(fn (callable $get) => match ($get('packaging_type')) {
                                'box'  => 'Box Stock Batch',
                                '5kg'  => '5kg Package Stock Batch',
                                'sack' => 'Sack Stock Batch',
                                default => 'Envelope (1kg) Stock Batch',
                            })
                            ->options(
                                RawMaterialStock::where('type', 'packaging staff')
                                    ->get()
                                    ->mapWithKeys(fn ($r) => [
                                        $r->id => "{$r->item} — Batch: {$r->batch_number} ({$r->quantity_in} remaining)",
                                    ])
                            )
                            ->searchable()
                            ->live(),

                        Forms\Components\Select::make('envelope_stock_id')
                            ->label('Envelope Batch (1kg packages inside boxes)')
                            ->helperText(fn (callable $get) => $get('item')
                                ? 'Will deduct ' . ((int) $get('item') * 12) . ' envelopes'
                                : 'Will deduct 12 envelopes per box')
                            ->options(
                                RawMaterialStock::where('type', 'packaging staff')
                                    ->get()
                                    ->mapWithKeys(fn ($r) => [
                                        $r->id => "{$r->item} — Batch: {$r->batch_number} ({$r->quantity_in} remaining)",
                                    ])
                            )
                            ->searchable()
                            ->live()
                            ->visible(fn (callable $get) => $get('packaging_type') === 'box')
                            ->required(fn (callable $get) => $get('packaging_type') === 'box'),
                    ]),

                Forms\Components\Section::make('Quantities & Pricing')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('item')
                            ->label(fn (callable $get) => match ($get('packaging_type')) {
                                'box'  => 'Number of Boxes',
                                '5kg'  => 'Number of 5kg Packages',
                                'sack' => 'Number of Sacks',
                                default => 'Number of 1kg Packages',
                            })
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $type = $get('packaging_type') ?? '1kg';
                                if ($type !== 'sack') {
                                    $set('quantity', (float) ($state ?? 0) * Emballage::packagingKg($type));
                                }
                                $set('total_price', (float) ($get('unit_price') ?? 0) * (float) ($state ?? 0));
                            }),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Total Flour (kg)')
                            ->numeric()
                            ->default(0)
                            ->readOnly(fn (callable $get) => $get('packaging_type') !== 'sack')
                            ->dehydrated()
                            ->helperText(fn (callable $get) => match ($get('packaging_type')) {
                                'box'   => 'Auto: Boxes × 12 kg',
                                '5kg'   => 'Auto: Packages × 5 kg',
                                'sack'  => 'Enter total weight in kg',
                                default => 'Auto: Units × 1 kg',
                            }),

                        Forms\Components\TextInput::make('unit_price')
                            ->label('Unit Price (RWF)')
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $set('total_price', (float) $state * (float) ($get('item') ?? 0));
                            }),

                        Forms\Components\TextInput::make('total_price')
                            ->label('Total Price (RWF)')
                            ->numeric()
                            ->readOnly()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('damaged')
                            ->label('Damaged Units')
                            ->numeric()
                            ->default(0),

                        Forms\Components\DatePicker::make('expiry_date')
                            ->label('Expiry Date')
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Forms\Components\Textarea::make('comment')
                            ->label('Comment / Notes')
                            ->maxLength(500)
                            ->rows(2)
                            ->columnSpan(2),

                        Forms\Components\Select::make('employee_id')
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
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('packaging_batch_id')->label('Batch ID')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('packaging_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'box'  => 'success',
                        '5kg'  => 'primary',
                        'sack' => 'warning',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'box'  => 'Box (12kg)',
                        '5kg'  => '5kg Pack',
                        'sack' => 'Sack',
                        default => '1kg Pack',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('milling.batch_number')->label('Milling Batch')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('item')->label('Units')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('quantity')->label('Flour (kg)')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('unit_price')->label('Unit Price')->numeric()->money('RWF')->sortable(),
                Tables\Columns\TextColumn::make('damaged')->numeric()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('employee.full_name')->label('Employee')->sortable()->searchable(),
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
            'index' => Pages\ListEmballages::route('/'),
        ];
    }
}
