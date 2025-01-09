<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Product extends Model
{
    //
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'product_name',
        'category_id',
        'image',
        'price',
    ];

    /**
     * Define the relationship to the Category.
     * Each product belongs to one category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function productComponents()
    {
        return $this->hasMany(ProductComponent::class);
    }


    /**
     * Define the relationship with BillingItem.
     * A product can appear in multiple billing items.
     */
    public function billingItems()
    {
        return $this->hasMany(BillingItem::class, 'product_id');
    }

    /**
     * Define the relationship with Inventory through ProductInventory pivot table.
     * A product can have multiple inventories (components) associated with it.
     */
    /*public function inventories()
    {
        return $this->belongsToMany(Inventory::class, 'product_inventory')
                    ->withPivot('quantity_required')
                    ->withTimestamps();
    }*/

    public function inventories()
    {
        return $this->belongsToMany(Inventory::class, 'product_components', 'product_id', 'inventory_id')
                    ->withPivot('quantity_required');
    }
    
}
