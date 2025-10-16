<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->json('trigger_conditions')->nullable(); // Conditions to start sequence
            $table->timestamps();
        });

        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequence_id')->nullable()->constrained('email_sequences')->onDelete('cascade');
            $table->string('name');
            $table->string('subject');
            $table->text('body_html');
            $table->text('body_text')->nullable();
            $table->integer('send_after_days')->default(0); // Days after sequence start
            $table->json('personalization_tags')->nullable(); // {{first_name}}, {{company}}, etc.
            $table->boolean('ab_test_enabled')->default(false);
            $table->foreignId('ab_test_variant_id')->nullable()->constrained('email_templates')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('email_sequence_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequence_id')->constrained('email_sequences')->onDelete('cascade');
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('current_step')->default(0);
            $table->timestamps();
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained('email_templates')->onDelete('set null');
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('enrollment_id')->nullable()->constrained('email_sequence_enrollments')->onDelete('set null');
            $table->string('subject');
            $table->text('body');
            $table->enum('status', ['sent', 'delivered', 'opened', 'clicked', 'bounced', 'failed'])->default('sent');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->integer('open_count')->default(0);
            $table->integer('click_count')->default(0);
            $table->json('tracking_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('email_sequence_enrollments');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('email_sequences');
    }
};

