<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Set; 
use Illuminate\Support\Str;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;


use Filament\Forms\Components\Repeater;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Products';
    protected static ?string $navigationGroup = 'Inventory Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(1)->schema([
                    Section::make('Product Information')->schema([
                        TextInput::make('product_name')
                            ->label('Product Name')
                            ->required()
                            ->maxLength(255),
    
                        Select::make('category_id')
                            ->label('Category')
                            ->options(
                                Category::query()
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->required(),
    
                        FileUpload::make('image')
                            ->image()
                            ->directory('products'),
    
                        TextInput::make('price')
                            ->label('Price')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01),
                    ]),
                    Section::make('Product Components')->schema([
                        Forms\Components\Repeater::make('productComponents')
                            ->label('Product Components')
                            ->relationship('productComponents') // Ensure a relationship is defined in the Product model
                            ->schema([
                                Select::make('inventory_id')
                                    ->label('Inventory Item')
                                    ->options(
                                        Inventory::all()->pluck('inventory_name', 'id')
                                    )
                                    ->required(),
    
                                TextInput::make('quantity_required')
                                    ->label('Quantity Required')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->createItemButtonLabel('Add Component'),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\ImageColumn::make('image'),

                TextColumn::make('category.name') // Use dot notation to access the related category name
                    ->label('Category')
                    ->sortable()
                    ->searchable(),
                    TextColumn::make('productComponents')
                    ->label('Components')
                    ->getStateUsing(function ($record) {
                        return $record->productComponents
                            ->map(fn ($component) => "{$component->inventory->inventory_name} ({$component->quantity_required})")
                            ->join(', ');
                    }),
                

                TextColumn::make('price')
                    ->label('Price')
                    ->sortable(),
            ])
            ->filters([
                // Add filters if needed
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
            // Define relations if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
