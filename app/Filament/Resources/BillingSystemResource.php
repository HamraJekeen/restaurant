<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillingSystemResource\Pages;
use App\Models\BillingSystem;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Carbon\Carbon;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;

class BillingSystemResource extends Resource
{
    protected static ?string $model = BillingSystem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Billing Management';
    protected static ?string $navigationLabel = 'Billing System';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(1)->schema([
                    Section::make('Billing Information')->schema([
                DatePicker::make('bill_date')
                    ->required()
                    ->default(now()),

                TimePicker::make('bill_time')
                    ->required()
                    ->default(now())
                    ->seconds(false),
                ]),
                Section::make('Billing items')->schema([

                Repeater::make('billingItems')
                    ->relationship()
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(Product::pluck('product_name', 'id'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $product = Product::find($state);
                                    $set('unit_price', $product?->price ?? 0);
                                }
                            }),

                        TextInput::make('order_quantity')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(20),

                        TextInput::make('unit_price')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('extra_price')
                            ->numeric()
                            ->default(0),

                        Textarea::make('description')
                            ->maxLength(255),

                        TextInput::make('total_price')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                    ])
                    ->columns(6),
                ]),
            ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('bill_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('bill_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->money('LKR')
                    ->sortable(),
                TextColumn::make('created_at')
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
            'index' => Pages\ListBillingSystems::route('/'),
            'create' => Pages\CreateBillingSystem::route('/create'),
            'edit' => Pages\EditBillingSystem::route('/{record}/edit'),
        ];
    }

    protected function afterCreate(): void
    {
        // Update total amount and inventory
        $record = $this->record;
        $totalAmount = $record->billingItems->sum('total_price');
        $record->update(['total_amount' => $totalAmount]);
        
        // Update inventory quantities
        foreach ($record->billingItems as $item) {
            $product = $item->product;
            foreach ($product->productComponents as $component) {
                $quantityToDecrease = $component->quantity_required * $item->order_quantity;
                $component->inventory->decreaseQuantity($quantityToDecrease);
            }
        }
    }
}
