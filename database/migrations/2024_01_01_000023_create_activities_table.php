<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Central activity log for all lead-related actions
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type'); // 'status_changed', 'assigned', 'email_sent', 'call_made', etc.
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // For UI display
            $table->string('color')->nullable(); // For UI display
            $table->json('metadata')->nullable(); // Additional data
            $table->morphs('activityable'); // Polymorphic relation to related model
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();
            
            $table->index(['lead_id', 'occurred_at']);
            $table->index(['user_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};

