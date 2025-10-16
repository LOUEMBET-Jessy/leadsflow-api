<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('organizer_user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('scheduled_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_minutes')->default(30);
            $table->string('location')->nullable();
            $table->string('meeting_url')->nullable(); // Zoom, Google Meet, etc.
            $table->enum('type', ['in_person', 'phone', 'video', 'other'])->default('video');
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('agenda')->nullable();
            $table->text('notes')->nullable();
            $table->text('action_items')->nullable();
            $table->enum('outcome', ['positive', 'neutral', 'negative'])->nullable();
            $table->string('calendar_event_id')->nullable(); // Google/Outlook Calendar ID
            $table->timestamps();
        });

        Schema::create('meeting_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['invited', 'accepted', 'declined', 'tentative'])->default('invited');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            
            $table->unique(['meeting_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_participants');
        Schema::dropIfExists('meetings');
    }
};

