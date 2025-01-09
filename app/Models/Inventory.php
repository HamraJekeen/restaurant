<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Alert;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_name',
        'inventory_quantity',
        'trigger_level',
        'inventory_price',
    ];

    public function decreaseQuantity(int $quantityUsed)
    {
        // Log the start of the operation
        \Log::info('Attempting to decrease inventory', [
            'inventory_name' => $this->inventory_name,
            'current_quantity' => $this->inventory_quantity,
            'decrease_by' => $quantityUsed,
            'trigger_level' => $this->trigger_level
        ]);

        // Check if there's enough inventory
        if ($this->inventory_quantity < $quantityUsed) {
            throw new \Exception("Insufficient quantity for {$this->inventory_name}. Need {$quantityUsed} but only have {$this->inventory_quantity}");
        }

        // Store the previous quantity
        $previousQuantity = $this->inventory_quantity;

        // Decrease the quantity
        $this->inventory_quantity = $previousQuantity - $quantityUsed;

        // Save the changes
        $this->save();

        \Log::info('Inventory decreased successfully', [
            'inventory_name' => $this->inventory_name,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $this->inventory_quantity,
            'decreased_by' => $quantityUsed
        ]);

        // Check if we've reached or gone below trigger level
        if ($this->inventory_quantity <= $this->trigger_level) {
            $this->createAlert();
        }

        return true;
    }

    protected function createAlert()
    {
        try {
            // Create the alert
            $alert = Alert::create([
                'alert_type' => 'trigger_level',
                'alert_message' => "WARNING: {$this->inventory_name} inventory is low! Current quantity: {$this->inventory_quantity}, Trigger level: {$this->trigger_level}",
                'inventory_id' => $this->id,
                'is_read' => false
            ]);

            // Set flash message
            session()->flash('warning', "Warning: {$this->inventory_name} has reached trigger level! Current quantity: {$this->inventory_quantity}, Trigger level: {$this->trigger_level}");

            \Log::info('Alert created successfully', [
                'inventory_name' => $this->inventory_name,
                'alert_id' => $alert->alert_id,
                'message' => $alert->alert_message
            ]);

            return $alert;
        } catch (\Exception $e) {
            \Log::error('Failed to create alert', [
                'inventory_name' => $this->inventory_name,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class, 'inventory_id');
    }

    public function productComponents()
    {
        return $this->hasMany(ProductComponent::class, 'inventory_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_components', 'inventory_id', 'product_id')
            ->withPivot('quantity_required')
            ->withTimestamps();
    }
}
