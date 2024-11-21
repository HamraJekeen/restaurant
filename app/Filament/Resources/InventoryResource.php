<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Filament\Resources\InventoryResource\RelationManagers;
use App\Models\Inventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\NumberInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
//use Filament\Forms\Components\NumberInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationLabel = 'Inventory';
    protected static ?string $navigationIcon = 'heroicon-o-bars-3-bottom-left';
    protected static ?string $navigationGroup = 'Inventory Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            TextInput::make('inventory_name')
                ->label('Inventory Name')
                ->required()
                ->maxLength(255),

            TextInput::make('inventory_quantity')
                ->label('Inventory Quantity')
                ->required()
                ->numeric() // Ensures only numbers are allowed
                ->rule('min:0'), // Min validation for numeric input

            TextInput::make('trigger_level')
                ->label('Trigger Level')
                ->required()
                ->numeric() // Ensures only numbers are allowed
                ->rule('min:0'), // Min validation for numeric input

            TextInput::make('inventory_price')
                ->label('Inventory Price')
                ->required()
                ->numeric() // Ensures only numbers are allowed
                ->rule('min:0') // Min validation for numeric input
                ->rule('max:99999999')
                ->step(0.01), // Allows decimal input

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            TextColumn::make('inventory_name')
                ->searchable()
                ->sortable(),

            TextColumn::make('inventory_quantity')
                ->sortable(),

            TextColumn::make('trigger_level')
                ->sortable(),

            TextColumn::make('inventory_price')
                ->money('LKR')
                ->sortable(),

            TextColumn::make('created_at')
                ->dateTime()
                ->sortable(),

            TextColumn::make('updated_at')
                ->dateTime()
                ->sortable(),
        
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
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
            'index' => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
