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
        Schema::create('penalty_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('penalty_code', 10)->unique()->comment('Short code for penalty (VW, WW, Pro, T)');
            $table->string('display_name', 100)->comment('Full display name for penalty');
            $table->string('short_label', 50)->comment('Short label for badges and compact displays');
            $table->string('badge_class', 50)->default('bg-secondary')->comment('CSS class for badge styling');
            $table->integer('sort_order')->default(0)->comment('Order for displaying penalties');
            $table->boolean('is_active')->default(true)->comment('Whether this penalty is currently active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalty_configurations');
    }
};
