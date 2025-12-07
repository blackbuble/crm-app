<?php
// database/migrations/xxxx_create_kpi_targets_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->enum('period_type', ['monthly', 'quarterly', 'yearly']);
            $table->integer('year');
            $table->integer('period'); // month (1-12), quarter (1-4), or year (same as year)
            
            // KPI Targets
            $table->decimal('revenue_target', 15, 2)->default(0);
            $table->integer('new_customers_target')->default(0);
            $table->integer('quotations_target')->default(0);
            $table->decimal('conversion_rate_target', 5, 2)->default(0); // percentage
            $table->integer('followups_target')->default(0);
            $table->decimal('win_rate_target', 5, 2)->default(0); // percentage
            
            // Actual Values (auto-calculated)
            $table->decimal('actual_revenue', 15, 2)->default(0);
            $table->integer('actual_new_customers')->default(0);
            $table->integer('actual_quotations')->default(0);
            $table->decimal('actual_conversion_rate', 5, 2)->default(0);
            $table->integer('actual_followups')->default(0);
            $table->decimal('actual_win_rate', 5, 2)->default(0);
            
            // Achievement percentage
            $table->decimal('achievement_percentage', 5, 2)->default(0);
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['user_id', 'period_type', 'year', 'period']);
            $table->index(['year', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_targets');
    }
};