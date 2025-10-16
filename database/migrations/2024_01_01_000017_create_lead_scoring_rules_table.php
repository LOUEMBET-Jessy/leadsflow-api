<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_scoring_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['demographic', 'behavioral', 'engagement', 'predictive'])->default('behavioral');
            $table->json('conditions'); // [{field: 'company_size', operator: '>', value: 100}]
            $table->integer('points');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // For rule ordering
            $table->timestamps();
        });

        Schema::create('lead_score_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('rule_id')->nullable()->constrained('lead_scoring_rules')->onDelete('set null');
            $table->integer('points_changed');
            $table->integer('score_before');
            $table->integer('score_after');
            $table->string('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // Add scoring fields to leads table
        Schema::table('leads', function (Blueprint $table) {
            $table->integer('behavioral_score')->default(0)->after('score');
            $table->integer('demographic_score')->default(0)->after('behavioral_score');
            $table->integer('engagement_score')->default(0)->after('demographic_score');
            $table->decimal('conversion_probability', 5, 2)->default(0)->after('engagement_score'); // ML prediction
            $table->timestamp('last_scored_at')->nullable()->after('conversion_probability');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'behavioral_score', 
                'demographic_score', 
                'engagement_score', 
                'conversion_probability',
                'last_scored_at'
            ]);
        });
        
        Schema::dropIfExists('lead_score_history');
        Schema::dropIfExists('lead_scoring_rules');
    }
};

