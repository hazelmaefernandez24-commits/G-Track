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
        // Only create if table doesn't exist
        if (!Schema::hasTable('inventory_history')) {
            Schema::create('inventory_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_item_id')->constrained('inventory')->onDelete('cascade');
                $table->string('user_id');
                $table->foreign('user_id')->references('user_id')->on('pnph_users');
                $table->string('action_type')->comment('add, remove, adjust, report');
                $table->decimal('quantity_change', 10, 2);
                $table->decimal('previous_quantity', 10, 2);
                $table->decimal('new_quantity', 10, 2);
                $table->text('notes')->nullable();
                $table->timestamps();

                // Add index for faster queries
                $table->index(['inventory_item_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_history');
    }
};
