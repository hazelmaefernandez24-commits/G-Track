<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * NOTE: This is intentionally a NO-OP migration.
	 * The `rotation_schedules` table is managed by the Login project
	 * (see: ../Login/database/migrations/16_2025_09_12_130500_create_rotation_schedules_table.php).
	 * Keeping this file prevents accidental attempts to create the same table from G16_CAPSTONE.
	 */
	public function up()
	{
		// intentionally left blank
	}

	public function down()
	{
		// intentionally left blank
	}
};
