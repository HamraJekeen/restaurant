<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('billing_systems', function (Blueprint $table) {
            $table->id();
            $table->date('bill_date');
            $table->time('bill_time');
            $table->decimal('total_amount', 10, 2)->nullable(); // Store the total amount for the bill
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_systems');
    }
};
