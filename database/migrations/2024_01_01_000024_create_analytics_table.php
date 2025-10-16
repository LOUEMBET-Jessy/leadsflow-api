<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Daily aggregated analytics
        Schema::create('daily_analytics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('cascade');
            $table->foreignId('pipeline_id')->nullable()->constrained('pipelines')->onDelete('cascade');
            
            // Lead metrics
            $table->integer('leads_created')->default(0);
            $table->integer('leads_qualified')->default(0);
            $table->integer('leads_converted')->default(0);
            $table->integer('leads_lost')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            
            // Activity metrics
            $table->integer('calls_made')->default(0);
            $table->integer('emails_sent')->default(0);
            $table->integer('meetings_held')->default(0);
            $table->integer('tasks_completed')->default(0);
            
            // Response time metrics
            $table->integer('avg_response_time_minutes')->default(0);
            $table->integer('avg_lead_age_days')->default(0);
            
            // Revenue metrics
            $table->decimal('revenue_generated', 15, 2)->default(0);
            $table->decimal('pipeline_value', 15, 2')->default(0);
            
            $table->timestamps();
            
            $table->unique(['date', 'user_id', 'team_id', 'pipeline_id']);
            $table->index('date');
        });

        // Performance benchmarks for ML
        Schema::create('performance_benchmarks', function (Blueprint $table) {
            $table->id();
            $table->string('metric_name');
            $table->decimal('value', 15, 2);
            $table->string('period'); // 'daily', 'weekly', 'monthly'
            $table->date('period_start');
            $table->date('period_end');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('cascade');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_benchmarks');
        Schema::dropIfExists('daily_analytics');
    }
};

