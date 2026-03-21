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
        // Create weekly_menu_dishes table
        if (!Schema::hasTable('weekly_menu_dishes')) {
            Schema::create('weekly_menu_dishes', function (Blueprint $table) {
                $table->id();
                $table->string('dish_name');
                $table->text('description')->nullable();
                $table->string('day_of_week'); // monday, tuesday, etc.
                $table->string('meal_type'); // breakfast, lunch, dinner
                $table->integer('week_cycle')->default(1); // 1 or 2
                $table->string('created_by'); // Cook who created it
                $table->timestamps();

                $table->foreign('created_by')->references('user_id')->on('pnph_users')->onDelete('cascade');
                $table->index(['week_cycle', 'day_of_week', 'meal_type']);
            });
        }

        // Create weekly_menu_dish_ingredients table (pivot table)
        if (!Schema::hasTable('weekly_menu_dish_ingredients')) {
            Schema::create('weekly_menu_dish_ingredients', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('weekly_menu_dish_id');
                $table->unsignedBigInteger('inventory_id');
                $table->decimal('quantity_used', 10, 2);
                $table->string('unit');
                $table->timestamps();

                $table->foreign('weekly_menu_dish_id', 'wmdi_dish_foreign')
                      ->references('id')->on('weekly_menu_dishes')->onDelete('cascade');
                $table->foreign('inventory_id', 'wmdi_inventory_foreign')
                      ->references('id')->on('inventory')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_menu_dish_ingredients');
        Schema::dropIfExists('weekly_menu_dishes');
    }
};
