<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;
    protected static ?string $navigationLabel = 'Clients & Suppliers';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-currency-bangladeshi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('full_name')
                    ->required()
                    ->maxLength(255),

Forms\Components\Select::make('client_type')
    ->label('Client Type')
    ->required()
    ->options([
        'Individual'  => 'Individual',
        'School'      => 'School',
        'Retailer'    => 'Retailer',
        'Wholesaler'  => 'Wholesaler',
        'Institution' => 'Institution',
        'Companies'   => 'Companies',
    ])
    ->searchable() // optional, allows typing to filter options
    ->default('Individual'), // optional default

                Forms\Components\Select::make('role')
                    ->required()
                    ->options([
                        'client' => 'Client',
                        'supplier' => 'Supplier',
                    ])
                    ->reactive(),

                Forms\Components\TextInput::make('supplier_code')
                    ->maxLength(255)
                    ->nullable()
                    ->visible(fn (callable $get) => $get('role') === 'supplier')
                    ->required(fn (callable $get) => $get('role') === 'supplier')
                    ->unique(Client::class, 'supplier_code', ignoreRecord: true),

                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255)
                    ->nullable(),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->nullable(),

Forms\Components\Select::make('district')
    ->label('District')
    ->required()
    ->options([
        'Bugesera'     => 'Bugesera',
        'Gatsibo'      => 'Gatsibo',
        'Kayonza'      => 'Kayonza',
        'Kirehe'       => 'Kirehe',
        'Ngoma'        => 'Ngoma',
        'Nyagatare'    => 'Nyagatare',
        'Rwamagana'    => 'Rwamagana',
        'Gasabo'       => 'Gasabo',
        'Kicukiro'     => 'Kicukiro',
        'Nyarugenge'   => 'Nyarugenge',
        'Burera'       => 'Burera',
        'Gakenke'      => 'Gakenke',
        'Gicumbi'      => 'Gicumbi',
        'Musanze'      => 'Musanze',
        'Rulindo'      => 'Rulindo',
        'Gisagara'     => 'Gisagara',
        'Huye'         => 'Huye',
        'Kamonyi'      => 'Kamonyi',
        'Muhanga'      => 'Muhanga',
        'Nyamagabe'    => 'Nyamagabe',
        'Nyanza'       => 'Nyanza',
        'Nyaruguru'    => 'Nyaruguru',
        'Ruhango'      => 'Ruhango',
        'Karongi'      => 'Karongi',
        'Ngororero'    => 'Ngororero',
        'Nyabihu'      => 'Nyabihu',
        'Nyamasheke'   => 'Nyamasheke',
        'Rubavu'       => 'Rubavu',
        'Rusizi'       => 'Rusizi',
        'Rutsiro'      => 'Rutsiro',
    ])
    ->searchable()
    ->placeholder('Select a district'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('supplier_code')
                    ->label('Supplier Code')
                    ->searchable()
                    ->toggleable(), // Hide by default if you want
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
