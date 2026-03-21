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
        Schema::create('severity_max_counts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('severity_id');
            $table->string('severity_name');
            $table->integer('max_count');
            $table->string('base_penalty', 10);
            $table->string('escalated_penalty', 10);
            $table->text('description')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('severity_id')->references('id')->on('severities')->onDelete('cascade');

            // Unique constraint to prevent duplicate severity entries
            $table->unique('severity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('severity_max_counts');
    }
};
