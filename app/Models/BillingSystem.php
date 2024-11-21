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
        'bill_amount',
    ];

    /**
     * Define the relationship to BillingItem.
     * Each bill can have multiple items/products.
     */
    public function billingItems()
    {
        return $this->hasMany(BillingItem::class, 'bill_id');
    }

    /**
     * Calculate the total bill amount based on all billing items.
     */
    public function calculateTotalBillAmount()
    {
        $this->bill_amount = $this->billingItems->sum(function ($billingItem) {
            return $billingItem->item_amount;
        });
        $this->save();
    }

    /**
     * Update inventory based on each billed item's quantity.
     */
    public function updateInventory()
    {
        foreach ($this->billingItems as $billingItem) {
            $product = $billingItem->product;

            if ($product) {
                foreach ($product->inventories as $inventory) {
                    $quantityUsed = $inventory->pivot->quantity_required * $billingItem->order_quantity;
                    $inventory->decreaseQuantity($quantityUsed);

                    // Check for trigger level alert
                    if ($inventory->isBelowTriggerLevel()) {
                        // Generate an alert (implementation depends on your alerting setup)
                        Alert::create([
                            'alert_type' => 'Low Inventory',
                            'alert_message' => "{$inventory->inventory_name} is below the trigger level.",
                        ]);
                    }
                }
            }
        }
    }
}
