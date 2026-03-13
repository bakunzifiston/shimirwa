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

                // Select Packaging Batch (Raw Material)
                Forms\Components\Select::make('raw_material_stock_id')
                    ->label('Packaging Type Batch')
                    ->options(
                        RawMaterialStock::where('type', 'packaging staff')
                            ->get()
                            ->mapWithKeys(fn ($r) => [
                                $r->id => "{$r->item} - {$r->batch_number} ({$r->quantity_in} left)"
                            ])
                    )
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $raw = RawMaterialStock::find($state);

                            if ($raw) {
                                $set('item', $raw->item);
                                $set('date', $raw->date);

                                if (!empty($raw->packaging_type)) {
                                    $set('packaging_type', $raw->packaging_type);
                                }
                            }
                        }
                    }),
                    Forms\Components\TextInput::make('packaging_batch_id')
    ->label('Packaging Batch ID')
    ->required(),


                // Select Milling Batch
                Forms\Components\Select::make('milling_id')
                    ->label('Milling Batch')
                    ->options(
                        Milling::all()->mapWithKeys(fn ($m) => [
                            $m->id => "{$m->batch_number} - {$m->total_mixed_quantity}kg (Output: {$m->output_flour}kg)"
                        ])
                    )
                    ->searchable()
                    ->reactive(),

                // ITEM
                Forms\Components\TextInput::make('item')
                    ->label('Item Number')
                    ->numeric()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {

                        $item = (float) ($state ?: 0);
                        $pack = preg_replace('/[^0-9]/', '', $get('packaging_type'));
                        $packValue = $pack ? (float)$pack : 1;

                        $set('quantity', $item * $packValue);
                    }),

                // PACKAGING TYPE
                Forms\Components\TextInput::make('packaging_type')
                    ->label('Packaging Size (kg)')
                    ->numeric()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {

                        $item = (float) ($get('item') ?: 0);
                        $pack = preg_replace('/[^0-9]/', '', $state);
                        $packValue = $pack ? (float)$pack : 1;

                        $set('quantity', $item * $packValue);
                    }),

                // QUANTITY (auto)
                Forms\Components\TextInput::make('quantity')
                    ->label('Total Quantity')
                    ->numeric()
                    ->default(0)
                    ->disabled()     // prevent manually editing
                    ->dehydrated(),  // still saved to database

                // DAMAGED
                Forms\Components\TextInput::make('damaged')
                    ->numeric()
                    ->default(0),

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
                Tables\Columns\TextColumn::make('packaging_batch_id')->label('Packaging Batch ID')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('milling.batch_number')->label('Batch Number')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('rawMaterialStock.item')->label('Packaging Type Name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('packaging_type')->label('Packaging Size (Kg)')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('item')->searchable()->label('Item Number'),
                
                Tables\Columns\TextColumn::make('quantity')->numeric()->sortable()->label('Total Quantity'),

                Tables\Columns\TextColumn::make('damaged')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('employee.full_name')->label('Employee')->sortable()->searchable(),

                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
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
            'index' => Pages\ListEmballages::route('/'),
            'create' => Pages\CreateEmballage::route('/create'),
            'view' => Pages\ViewEmballage::route('/{record}'),
            'edit' => Pages\EditEmballage::route('/{record}/edit'),
        ];
    }
}
