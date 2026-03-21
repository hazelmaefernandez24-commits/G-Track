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
        Schema::table('categories', function (Blueprint $table) {
            // Check if description column doesn't exist and add it
            if (!Schema::hasColumn('categories', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            
            // Check if parent_id column doesn't exist and add it
            if (!Schema::hasColumn('categories', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('description');
                $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
                $table->index('parent_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['parent_id']);
            
            // Drop the columns
            $table->dropColumn(['description', 'parent_id']);
        });
    }
};
