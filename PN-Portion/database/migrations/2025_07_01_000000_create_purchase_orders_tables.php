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
        // Create purchase_orders table
        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number')->unique();
                $table->string('created_by'); // Cook who created the order
                $table->enum('status', ['pending', 'approved', 'ordered', 'delivered', 'cancelled'])->default('pending');
                $table->date('order_date');
                $table->date('expected_delivery_date')->nullable();
                $table->date('actual_delivery_date')->nullable();
                $table->decimal('total_amount', 10, 2)->default(0);
                $table->text('notes')->nullable();
                $table->string('approved_by')->nullable(); // Cook/Admin who approved
                $table->timestamp('approved_at')->nullable();
                $table->string('delivered_by')->nullable(); // Kitchen staff who confirmed delivery
                $table->timestamp('delivered_at')->nullable();
                $table->timestamps();

                $table->foreign('created_by')->references('user_id')->on('pnph_users')->onDelete('cascade');
                $table->foreign('approved_by')->references('user_id')->on('pnph_users')->onDelete('set null');
                $table->foreign('delivered_by')->references('user_id')->on('pnph_users')->onDelete('set null');
            });
        }

        // Create purchase_order_items table
        if (!Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_order_id');
                $table->unsignedBigInteger('inventory_id'); // Link to inventory items
                $table->string('item_name'); // Store name for reference
                $table->decimal('quantity_ordered', 10, 2);
                $table->decimal('quantity_delivered', 10, 2)->default(0);
                $table->string('unit');
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total_price', 10, 2);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
                $table->foreign('inventory_id')->references('id')->on('inventory')->onDelete('cascade');
            });
        }

        // Note: We're not creating suppliers table as requested - focusing only on items

        // Add relationship between meals and inventory items
        if (!Schema::hasTable('meal_ingredients')) {
            Schema::create('meal_ingredients', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('meal_id');
                $table->unsignedBigInteger('inventory_id');
                $table->decimal('quantity_per_serving', 10, 3); // Amount needed per serving
                $table->string('unit');
                $table->timestamps();

                $table->foreign('meal_id')->references('id')->on('meals')->onDelete('cascade');
                $table->foreign('inventory_id')->references('id')->on('inventory')->onDelete('cascade');
                $table->unique(['meal_id', 'inventory_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_ingredients');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        // Don't drop suppliers table as it might be used elsewhere
    }
};
