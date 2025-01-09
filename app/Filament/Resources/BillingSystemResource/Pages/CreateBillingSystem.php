<?php

namespace App\Filament\Resources\BillingSystemResource\Pages;

use App\Filament\Resources\BillingSystemResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBillingSystem extends CreateRecord
{
    protected static string $resource = BillingSystemResource::class;

    protected function afterCreate(): void
    {
        try {
            // Get the fresh record with relationships
            $record = $this->record->fresh(['billingItems.product.productComponents.inventory']);
            
            // Calculate and update total amount
            $totalAmount = $record->billingItems->sum('total_price');
            
            // Update the record with the total amount
            $record->update([
                'total_amount' => $totalAmount
            ]);

            \Log::info('Processing billing items for inventory updates');

            // Update inventory quantities
            foreach ($record->billingItems as $item) {
                $product = $item->product;
                foreach ($product->productComponents as $component) {
                    $quantityToDecrease = $component->quantity_required * $item->order_quantity;
                    \Log::info('Updating inventory for component', [
                        'product' => $product->product_name,
                        'component' => $component->inventory->inventory_name,
                        'quantity_to_decrease' => $quantityToDecrease
                    ]);
                    $component->inventory->decreaseQuantity($quantityToDecrease);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error in afterCreate:', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
