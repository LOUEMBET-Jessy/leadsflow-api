<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('segments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('criteria'); // Dynamic filtering criteria
            $table->enum('type', ['static', 'dynamic'])->default('dynamic');
            $table->boolean('is_public')->default(false);
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
            $table->integer('lead_count')->default(0);
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lead_segment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('segment_id')->constrained()->onDelete('cascade');
            $table->timestamp('added_at')->useCurrent();
            
            $table->unique(['lead_id', 'segment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_segment');
        Schema::dropIfExists('segments');
    }
};

