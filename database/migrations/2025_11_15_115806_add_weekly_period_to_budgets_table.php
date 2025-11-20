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
        // Change the enum to include 'weekly'
        DB::statement("ALTER TABLE budgets MODIFY COLUMN period ENUM('weekly', 'monthly', 'yearly') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum
        DB::statement("ALTER TABLE budgets MODIFY COLUMN period ENUM('monthly', 'yearly') NOT NULL");
    }
};
