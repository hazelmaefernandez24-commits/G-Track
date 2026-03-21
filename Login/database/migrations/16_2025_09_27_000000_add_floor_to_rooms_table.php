<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		// Add a nullable 'floor' integer column to the rooms table.
		// Guard with Schema::hasColumn to avoid errors if the column already exists
		if (!Schema::hasColumn('rooms', 'floor')) {
			Schema::table('rooms', function (Blueprint $table) {
				// Place after room_number for readability; nullable so existing rows are unaffected
				$table->integer('floor')->nullable()->after('room_number')->comment('Numeric floor number, e.g. 2 for second floor');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		// Remove the 'floor' column if it exists
		if (Schema::hasColumn('rooms', 'floor')) {
			Schema::table('rooms', function (Blueprint $table) {
				$table->dropColumn('floor');
			});
		}
	}
};

