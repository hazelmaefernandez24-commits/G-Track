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
        Schema::create('category_limits', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->unsignedInteger('max_total');
            $table->unsignedInteger('max_boys')->nullable();
            $table->unsignedInteger('max_girls')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_limits');
    }
};