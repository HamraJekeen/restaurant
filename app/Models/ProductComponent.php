<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductComponent extends Model
{
    //
    use HasFactory;

    protected $table = 'product_components';

    protected $fillable = [
        'product_id',
        'inventory_id',
        'quantity_required',
    ];

    /**
     * Define the relationship to the Product.
     * Each ProductComponent belongs to a specific product.
     */
    
    public function inventory()
    {
    return $this->belongsTo(Inventory::class,'inventory_id');
                //->withPivot('quantity_required');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    /*protected static function booted()
    {
        static::creating(function ($productComponent) {
            // Check if the product exists, if not, create it
            if (!$productComponent->product_id) {
                $product = Product::create([
                    'product_name' => 'Auto Created Product',  // You can modify this to be dynamic based on the component
                    'category_id' => 1,  // You can set a default category or link it to the product component
                    'price' => 0,  // Set a default price, or adjust as needed
                ]);
                
                // Assign the created product's ID to the product component
                $productComponent->product_id = $product->id;
            }
        });
    }*/

    /**
     * Define the relationship to the Inventory.
     * Each ProductComponent is related to a specific inventory item.
     */
    
}
