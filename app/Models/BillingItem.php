<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'product_id',
        'order_quantity',
        'unit_price',
        'extra_price',
        'description',
        'total_price',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'extra_price' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::created(function ($billingItem) {
            // Update the bill's total amount when a new item is added
            $bill = $billingItem->bill;
            $totalAmount = $bill->billingItems->sum('total_price');
            $bill->update(['total_amount' => $totalAmount]);
        });
    }

    public function bill()
    {
        return $this->belongsTo(BillingSystem::class, 'bill_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
