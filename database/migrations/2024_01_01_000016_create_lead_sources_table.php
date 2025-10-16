<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "LinkedIn Ads Campaign Q1", "Website Form - Homepage"
            $table->enum('type', ['website_form', 'email', 'social_media', 'import', 'api', 'scraping', 'referral', 'event', 'other'])->default('other');
            $table->string('external_id')->nullable(); // For integration with external platforms
            $table->json('config')->nullable(); // Configuration for capture (form fields, API keys, etc.)
            $table->boolean('is_active')->default(true);
            $table->integer('total_leads')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0); // Percentage
            $table->decimal('average_score', 5, 2)->default(0);
            $table->json('performance_metrics')->nullable();
            $table->timestamps();
        });

        // Add source_id to leads table
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('source_id')->nullable()->after('source')->constrained('lead_sources')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['source_id']);
            $table->dropColumn('source_id');
        });
        
        Schema::dropIfExists('lead_sources');
    }
};

