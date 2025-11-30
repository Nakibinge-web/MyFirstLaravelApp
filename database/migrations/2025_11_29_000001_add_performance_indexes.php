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
        // Add indexes for better query performance
        
        // Transactions table indexes
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'idx_transactions_user_date');
            $table->index(['user_id', 'type'], 'idx_transactions_user_type');
            $table->index(['user_id', 'category_id'], 'idx_transactions_user_category');
            $table->index(['date'], 'idx_transactions_date');
        });

        // Budgets table indexes
        Schema::table('budgets', function (Blueprint $table) {
            $table->index(['user_id', 'start_date', 'end_date'], 'idx_budgets_user_dates');
            $table->index(['user_id', 'category_id'], 'idx_budgets_user_category');
        });

        // Goals table indexes
        Schema::table('goals', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'idx_goals_user_status');
            $table->index(['user_id', 'target_date'], 'idx_goals_user_target_date');
        });

        // Categories table indexes
        Schema::table('categories', function (Blueprint $table) {
            $table->index(['user_id', 'type'], 'idx_categories_user_type');
        });

        // Notifications table indexes
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read'], 'idx_notifications_user_read');
            $table->index(['user_id', 'created_at'], 'idx_notifications_user_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_user_date');
            $table->dropIndex('idx_transactions_user_type');
            $table->dropIndex('idx_transactions_user_category');
            $table->dropIndex('idx_transactions_date');
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->dropIndex('idx_budgets_user_dates');
            $table->dropIndex('idx_budgets_user_category');
        });

        Schema::table('goals', function (Blueprint $table) {
            $table->dropIndex('idx_goals_user_status');
            $table->dropIndex('idx_goals_user_target_date');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_user_type');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_read');
            $table->dropIndex('idx_notifications_user_created');
        });
    }
};
