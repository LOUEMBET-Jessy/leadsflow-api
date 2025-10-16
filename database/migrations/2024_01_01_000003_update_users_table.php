<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('set null');
            $table->string('two_factor_secret')->nullable();
            $table->foreignId('current_team_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->string('profile_photo_path')->nullable();
            $table->json('settings')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['team_id']);
            $table->dropForeign(['current_team_id']);
            $table->dropColumn([
                'role_id',
                'team_id',
                'two_factor_secret',
                'current_team_id',
                'profile_photo_path',
                'settings'
            ]);
        });
    }
};
