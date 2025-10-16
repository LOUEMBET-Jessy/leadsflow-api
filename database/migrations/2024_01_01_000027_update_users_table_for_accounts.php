<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('account_id')->after('id')->constrained()->onDelete('cascade');
            $table->enum('role', ['Admin', 'Manager', 'Commercial', 'Marketing', 'GestLead'])->after('password')->default('Commercial');
            $table->string('phone')->after('role')->nullable();
            $table->string('avatar')->after('phone')->nullable();
            $table->json('settings')->after('avatar')->nullable();
            $table->boolean('is_active')->after('settings')->default(true);
            $table->timestamp('last_login_at')->after('is_active')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn([
                'account_id',
                'role',
                'phone',
                'avatar',
                'settings',
                'is_active',
                'last_login_at'
            ]);
        });
    }
};
