<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('phone_number');
            $table->enum('direction', ['inbound', 'outbound']);
            $table->enum('status', ['completed', 'missed', 'failed', 'no_answer', 'voicemail'])->default('completed');
            $table->integer('duration_seconds')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('recording_url')->nullable(); // S3/Cloud storage URL
            $table->text('transcription')->nullable(); // AI transcription
            $table->json('sentiment_analysis')->nullable(); // AI sentiment
            $table->json('key_points')->nullable(); // AI-extracted key points
            $table->enum('outcome', ['positive', 'neutral', 'negative', 'follow_up_required'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};

