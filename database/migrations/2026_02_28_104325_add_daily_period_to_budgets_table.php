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
        // Change the enum to include 'daily'
        DB::statement("ALTER TABLE budgets MODIFY COLUMN period ENUM('daily', 'weekly', 'monthly', 'yearly') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to previous enum
        DB::statement("ALTER TABLE budgets MODIFY COLUMN period ENUM('weekly', 'monthly', 'yearly') NOT NULL");
    }
};
