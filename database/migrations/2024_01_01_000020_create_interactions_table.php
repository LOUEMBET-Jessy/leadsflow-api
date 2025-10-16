<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['Email', 'Appel', 'Reunion', 'Note', 'SMS', 'Chat'])->default('Note');
            $table->string('subject')->nullable();
            $table->text('summary')->nullable();
            $table->longText('details')->nullable();
            $table->datetime('date');
            $table->integer('duration')->nullable(); // En minutes
            $table->enum('outcome', ['positive', 'neutral', 'negative', 'follow_up_required'])->nullable();
            $table->json('metadata')->nullable(); // Données additionnelles (fichiers joints, etc.)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interactions');
    }
};
