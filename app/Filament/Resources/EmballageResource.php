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

                Forms\Components\DatePicker::make('date')
                    ->required(),

                Forms\Components\TextInput::make('packaging_batch_id')
                    ->label('Packaging Batch ID')
                    ->required(),

                // Packaging Type — fixed dropdown
                Forms\Components\Select::make('packaging_type')
                    ->label('Packaging Type')
                    ->options([
                        'box'  => 'Box (1 box = 12 × 1kg packages = 12kg)',
                        '1kg'  => '1kg Package (individual envelope)',
                        '5kg'  => '5kg Package (individual 5kg unit)',
                        'sack' => 'Sack (flexible — enter weight manually)',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $item  = (float) ($get('item') ?? 0);
                        $type  = $state ?? '1kg';
                        // Sack: clear auto-qty so user enters it freely
                        if ($type === 'sack') {
                            $set('quantity', null);
                        } else {
                            $set('quantity', $item * Emballage::packagingKg($type));
                        }
                        $set('envelope_stock_id', null);
                    }),

                // Primary packaging material batch (box / envelope / 5kg / sack stock)
                Forms\Components\Select::make('raw_material_stock_id')
                    ->label(fn (callable $get) => match($get('packaging_type')) {
                        'box'  => 'Box Batch (select box stock)',
                        '5kg'  => '5kg Package Batch (select 5kg package stock)',
                        'sack' => 'Sack Batch (select sack stock)',
                        default => 'Envelope Batch (1kg package stock)',
                    })
                    ->options(
                        RawMaterialStock::where('type', 'packaging staff')
                            ->get()
                            ->mapWithKeys(fn ($r) => [
                                $r->id => "{$r->item} — Batch: {$r->batch_number} ({$r->quantity_in} remaining)"
                            ])
                    )
                    ->searchable()
                    ->reactive(),

                // Envelope batch — only shown when packaging type is Box
                Forms\Components\Select::make('envelope_stock_id')
                    ->label('Envelope Batch (1kg packages to use inside boxes)')
                    ->helperText(fn (callable $get) => $get('item')
                        ? 'Will deduct ' . ((int)$get('item') * 12) . ' envelopes from this stock'
                        : 'Will deduct 12 envelopes per box')
                    ->options(
                        RawMaterialStock::where('type', 'packaging staff')
                            ->get()
                            ->mapWithKeys(fn ($r) => [
                                $r->id => "{$r->item} — Batch: {$r->batch_number} ({$r->quantity_in} remaining)"
                            ])
                    )
                    ->searchable()
                    ->reactive()
                    ->visible(fn (callable $get) => $get('packaging_type') === 'box')
                    ->required(fn (callable $get) => $get('packaging_type') === 'box'),

                // Milling Batch
                Forms\Components\Select::make('milling_id')
                    ->label('Milling Batch (flour source)')
                    ->options(
                        Milling::all()->mapWithKeys(fn ($m) => [
                            $m->id => "{$m->batch_number} — Output: {$m->output_flour}kg available"
                        ])
                    )
                    ->searchable()
                    ->reactive(),

                // Number of units
                Forms\Components\TextInput::make('item')
                    ->label(fn (callable $get) => match($get('packaging_type')) {
                        'box'  => 'Number of Boxes',
                        '5kg'  => 'Number of 5kg Packages',
                        'sack' => 'Number of Sacks',
                        default => 'Number of 1kg Packages',
                    })
                    ->numeric()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $item = (float) ($state ?? 0);
                        $type = $get('packaging_type') ?? '1kg';
                        // Sack: don't auto-set quantity; user enters it manually
                        if ($type !== 'sack') {
                            $set('quantity', $item * Emballage::packagingKg($type));
                        }
                    }),

                // Total flour kg — auto for fixed types, editable for Sack
                Forms\Components\TextInput::make('quantity')
                    ->label('Total Flour (kg)')
                    ->numeric()
                    ->default(0)
                    ->disabled(fn (callable $get) => $get('packaging_type') !== 'sack')
                    ->dehydrated()
                    ->helperText(fn (callable $get) => match($get('packaging_type')) {
                        'box'  => 'Auto: Boxes × 12 kg',
                        '5kg'  => 'Auto: Packages × 5 kg',
                        'sack' => 'Enter total weight in kg (sack size varies)',
                        default => 'Auto: Units × 1 kg',
                    }),

                Forms\Components\TextInput::make('damaged')
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('unit_price')
                    ->label('Unit Price (RWF)')
                    ->numeric()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('total_price', (float)$state * (float)($get('item') ?? 0));
                    }),

                Forms\Components\TextInput::make('total_price')
                    ->label('Total Price (RWF)')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\DatePicker::make('expiry_date'),

                Forms\Components\Textarea::make('comment')
                    ->maxLength(500)
                    ->label('Comment / Notes'),

                Forms\Components\Select::make('employee_id')
                    ->relationship('employee', 'full_name')
                    ->searchable()
                    ->required()
                    ->label('Responsible Employee'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')->date()->sortable(),
                Tables\Columns\TextColumn::make('packaging_batch_id')
                    ->label('Batch ID')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('packaging_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state) => match($state) {
                        'box'  => 'success',
                        '5kg'  => 'primary',
                        'sack' => 'warning',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (string $state) => match($state) {
                        'box'  => 'Box (12kg)',
                        '5kg'  => '5kg Package',
                        'sack' => 'Sack (flexible)',
                        default => '1kg Package',
                    })
                    ->sortable()->searchable(),
                Tables\Columns\TextColumn::make('milling.batch_number')
                    ->label('Milling Batch')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('item')
                    ->label('Units')
                    ->numeric()->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Total Flour (kg)')
                    ->numeric()->sortable(),
                Tables\Columns\TextColumn::make('rawMaterialStock.item')
                    ->label('Primary Stock')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('envelopeStock.item')
                    ->label('Envelope Stock')
                    ->sortable()->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('damaged')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('employee.full_name')
                    ->label('Employee')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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
            'index'  => Pages\ListEmballages::route('/'),
            'create' => Pages\CreateEmballage::route('/create'),
            'view'   => Pages\ViewEmballage::route('/{record}'),
            'edit'   => Pages\EditEmballage::route('/{record}/edit'),
        ];
    }
}
