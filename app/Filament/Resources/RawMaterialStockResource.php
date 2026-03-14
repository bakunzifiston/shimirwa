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
                Forms\Components\DatePicker::make('date')
                    ->required(),
                
                    BelongsToSelect::make('client_id')
                    ->relationship('client', 'full_name')
                    ->label('Supplier')
                    ->searchable()
                    ->required(),    
                    
Forms\Components\Select::make('type')

    ->label('Type')
    ->options([
        'Raw Material'    => 'Raw Material',
        'Packaging Staff' => 'Packaging Staff',
        'Other'           => 'Other',
    ])
    ->required()
    ->reactive(),

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
                '5kg' => '5kg',
                '1kg' => '1kg',
                'Box' => 'Box',
                'Sack' => 'Sack',
              
            ],
            default => [],
        };
    })
    ->required()
    ->searchable()
    ->reactive()
    ->hidden(fn (callable $get) => $get('type') === 'Other'), // hide Select when Other

Forms\Components\TextInput::make('item')
    ->label('Item')
    ->placeholder('Enter custom item')
    ->required(fn (callable $get) => $get('type') === 'Other')
    ->hidden(fn (callable $get) => $get('type') !== 'Other'), // show TextInput only when Other

                Forms\Components\TextInput::make('received')
                    ->label('Received Quantity')
                    ->required()
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('rejected')
                    ->label('Rejected Quantity')
                    ->required()
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('quantity_in')
                    ->label('Net Quantity In')
                    ->numeric()
                    ->disabled() // read-only
                    ->default(0)
                    ->helperText('Automatically calculated: received - rejected'),

                Forms\Components\TextInput::make('comment')
                    ->maxLength(255)
                    ->nullable(),

                Forms\Components\TextInput::make('batch_number')
                    ->required()
                    ->maxLength(255),

                BelongsToSelect::make('employee_id')
                    ->relationship('employee', 'full_name')
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
                    TextColumn::make('client.full_name')
                    ->label('Supplier')
                    ->sortable()
                    ->searchable(), 
             TextColumn::make('type')   // <-- added type column
                    ->searchable()
                    ->sortable(),        

                TextColumn::make('item')
                    ->searchable(),

                TextColumn::make('received')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('rejected')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('quantity_in')
                    ->label('Net Quantity In')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('batch_number')
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
            'index' => Pages\ListRawMaterialStocks::route('/'),
            'create' => Pages\CreateRawMaterialStock::route('/create'),
            'view' => Pages\ViewRawMaterialStock::route('/{record}'),
            'edit' => Pages\EditRawMaterialStock::route('/{record}/edit'),
        ];
    }
}
