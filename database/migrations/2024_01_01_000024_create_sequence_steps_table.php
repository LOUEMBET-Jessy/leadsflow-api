<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequence_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequence_id')->constrained('email_sequences')->onDelete('cascade');
            $table->integer('order');
            $table->integer('delay_days')->default(0); // Délai en jours après l'étape précédente
            $table->string('subject');
            $table->longText('email_template'); // Template HTML
            $table->text('text_template')->nullable(); // Version texte
            $table->json('personalization_tags')->nullable(); // Tags de personnalisation
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequence_steps');
    }
};
