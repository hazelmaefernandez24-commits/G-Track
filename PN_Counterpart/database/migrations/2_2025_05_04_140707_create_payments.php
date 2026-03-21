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
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id'); // Auto-incrementing primary key
            $table->string('student_id', 20); // Foreign key to students table
            $table->float('amount'); // Payment amount
            $table->date('payment_date'); // Date of payment
            $table->string('payment_mode', 50)->nullable(); // Payment mode (optional)
            $table->string('payment_proof', 255)->nullable(); // Proof of payment (optional)
            $table->string('reference_number', 255)->nullable(); // Reference number (optional)
            $table->string('status', 20)->default('Pending'); // Payment status
            $table->string('verified_by')->nullable(); // Foreign key to pnph_users table
            $table->timestamps(); // Adds created_at and updated_at columns

            // Foreign key constraints
            $table->foreign('student_id')->references('student_id')->on('student_details')->onDelete('cascade');
            $table->foreign('verified_by')->references('user_id')->on('pnph_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
