<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;


class Inventory extends Model
{
    //
    use HasFactory;

    protected $primaryKey = 'id';

    // Define the fillable properties for mass assignment
    protected $fillable = [
        'inventory_name',
        'inventory_quantity',
        'trigger_level',
        'inventory_price',
    ];

    /**
     * Method to check if the inventory is below the trigger level.
     *
     * @return bool
     */
    public function isBelowTriggerLevel()
    {
        return $this->inventory_quantity <= $this->trigger_level;
    }

    /**
     * Decrease the inventory quantity based on the quantity used.
     *
     * @param int $quantityUsed
     * @return void
     */
    public function decreaseQuantity(int $quantityUsed)
    {
        $this->inventory_quantity -= $quantityUsed;
        $this->save();
    }

    /**
     * Define any relationships here if needed, for example, with products.**/
    /* 
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_inventory')
                    ->withPivot('quantity_required');
    }
    public function components()
    {
    return $this->hasMany(ProductComponent::class);
    }*/
    public function productComponents()
    {
        return $this->hasMany(ProductComponent::class, 'inventory_id');
    }

    /**
     * Define the relationship with Products through ProductComponents.
     * An Inventory item can be associated with multiple Products through ProductComponents.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_components', 'inventory_id', 'product_id')
                    ->withPivot('quantity_required')
                    ->withTimestamps();
    }
}
