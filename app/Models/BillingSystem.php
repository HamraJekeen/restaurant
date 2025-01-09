<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillingSystem extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'bill_date',
        'bill_time',
        'total_amount',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'bill_time' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function updateInventory()
    {
        \Log::info('Starting inventory update for billing: ' . $this->id);
        
        foreach ($this->billingItems as $billingItem) {
            $product = $billingItem->product;
            \Log::info('Processing product:', [
                'product_name' => $product->product_name,
                'order_quantity' => $billingItem->order_quantity
            ]);
            
            // Get all components for this product
            $productComponents = $product->productComponents()->with('inventory')->get();
            
            \Log::info('Product components found:', [
                'product' => $product->product_name,
                'components' => $productComponents->map(function($component) {
                    return [
                        'inventory_name' => $component->inventory->inventory_name,
                        'quantity_required' => $component->quantity_required,
                        'current_inventory' => $component->inventory->inventory_quantity
                    ];
                })
            ]);

            foreach ($productComponents as $component) {
                $inventory = $component->inventory;
                $quantityToDecrease = $component->quantity_required * $billingItem->order_quantity;
                
                \Log::info('Updating inventory:', [
                    'inventory_name' => $inventory->inventory_name,
                    'quantity_required_per_product' => $component->quantity_required,
                    'order_quantity' => $billingItem->order_quantity,
                    'total_quantity_to_decrease' => $quantityToDecrease,
                    'current_inventory' => $inventory->inventory_quantity
                ]);

                try {
                    if ($inventory->inventory_quantity < $quantityToDecrease) {
                        throw new \Exception(
                            "Insufficient inventory for {$inventory->inventory_name}. " .
                            "Need {$quantityToDecrease}, but only have {$inventory->inventory_quantity}"
                        );
                    }

                    $previousQuantity = $inventory->inventory_quantity;
                    $inventory->inventory_quantity -= $quantityToDecrease;
                    $inventory->save();

                    \Log::info('Inventory updated successfully', [
                        'inventory_name' => $inventory->inventory_name,
                        'previous_quantity' => $previousQuantity,
                        'decreased_by' => $quantityToDecrease,
                        'new_quantity' => $inventory->inventory_quantity
                    ]);

                } catch (\Exception $e) {
                    \Log::error('Error updating inventory:', [
                        'error' => $e->getMessage(),
                        'inventory' => $inventory->inventory_name
                    ]);
                    throw $e;
                }
            }
        }
    }

    public function billingItems()
    {
        return $this->hasMany(BillingItem::class, 'bill_id');
    }

    protected static function booted()
    {
        static::created(function ($billingSystem) {
            // Calculate total amount when created
            $totalAmount = $billingSystem->billingItems->sum('total_price');
            $billingSystem->update(['total_amount' => $totalAmount]);
        });
    }
}
