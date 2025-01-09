<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductComponentResource\Pages;
use App\Models\ProductComponent;
use App\Models\Inventory;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
class ProductComponentResource extends Resource
{
    protected static ?string $model = ProductComponent::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'Inventory Management';
    protected static ?string $navigationLabel = 'Product component';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    Section::make('Product Component Information')->schema([
                        Select::make('product_id')
                            ->label('Product')
                            //->relationship('product', 'product_name') // Assuming product is the related model and product_name is the field to display
                            ->options(
                                Product::query()
                                    ->whereNotNull('product_name') // Use 'product_name', as defined in the migration
                                    ->pluck('product_name', 'id') // Adjust column name
                            )
                            ->required(),

                            Select::make('inventory_id')
                                ->label('Inventory')
                                ->options(Inventory::all()->pluck('inventory_name', 'id'))
                                ->required(),
                            TextInput::make('quantity_required')
                                ->label('Quantity Required')
                                ->numeric()
                                ->minValue(1)
                                ->required(),
                    ]),

                ]),
            ]);
            
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.product_name')
                    ->label('Product Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('inventory.inventory_name')
                    ->label('Inventory Item')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('quantity_required')
                    ->label('Quantity Required')
                    ->sortable(),

                
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => Pages\ListProductComponents::route('/'),
            'create' => Pages\CreateProductComponent::route('/create'),
            'edit' => Pages\EditProductComponent::route('/{record}/edit'),
        ];
    }
}