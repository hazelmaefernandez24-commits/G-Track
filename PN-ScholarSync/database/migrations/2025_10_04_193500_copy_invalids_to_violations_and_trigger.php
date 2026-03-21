<?php

use Illuminate\Database\Migrations\Migration;

// Neutralized per latest requirement: do NOT write invalids into 'violations' and do NOT create triggers.
// This migration intentionally does nothing.
return new class extends Migration {
    public function up(): void {}
    public function down(): void {}
};
