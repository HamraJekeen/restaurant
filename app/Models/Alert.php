<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Alert extends Model
{
    //
    use HasFactory;

    // Define the table name if it's different from the plural form of the model name
    protected $table = 'alerts';

    // Define the primary key if it's different from 'id'
    protected $primaryKey = 'alert_id';

    // If the table does not use timestamps, set this to false (by default it's true)
    public $timestamps = true;

    // Define the fillable attributes for mass assignment
    protected $fillable = [
        'alert_type',
        'alert_message',
        'inventory_id',
    ];

    // Define the relationship to the Inventory model
    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'id');
    }
}
