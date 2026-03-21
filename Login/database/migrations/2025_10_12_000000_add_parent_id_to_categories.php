<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categories') && !Schema::hasColumn('categories', 'parent_id')) {
            Schema::table('categories', function (Blueprint $table) {
                // categories.id uses ->id() (unsignedBigInteger), so match that here
                $table->unsignedBigInteger('parent_id')->nullable()->after('id');
                $table->index('parent_id');
                $table->foreign('parent_id')->references('id')->on('categories')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'parent_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropForeign([$table->getTable() ? 'parent_id' : 'parent_id']);
                $table->dropIndex(['parent_id']);
                $table->dropColumn('parent_id');
            });
        }
    }
};
