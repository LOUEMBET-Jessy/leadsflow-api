<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_type'); // lead_created, status_changed, score_updated, etc.
            $table->string('action_type'); // send_email, assign_user, update_status, etc.
            $table->json('parameters'); // Conditions et paramètres de l'action
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Ordre d'exécution
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};
