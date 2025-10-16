<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->foreignId('current_stage_id')->nullable()->constrained('stages')->onDelete('set null');
            $table->string('name');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('company')->nullable();
            $table->enum('status', [
                'Nouveau', 'Contacté', 'Qualification', 'Négociation', 
                'Gagné', 'Perdu', 'Chaud', 'Froid', 'A_recontacter', 'Non_qualifié'
            ])->default('Nouveau');
            $table->string('source', 100)->nullable();
            $table->string('location')->nullable();
            $table->integer('score')->default(0);
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable(); // Champs personnalisés
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
