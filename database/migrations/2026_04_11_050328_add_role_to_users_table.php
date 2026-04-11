<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations (apply changes).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('networking_opt_in')->default(false);
            $table->string('profile_photo')->nullable();
        });
    }

    /**
     * Reverse the migrations (rollback).
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key first (important!)
            $table->dropForeign(['role_id']);
            
            // Then drop columns
            $table->dropColumn([
                'role_id',
                'phone',
                'company',
                'job_title',
                'bio',
                'networking_opt_in',
                'profile_photo',
            ]);
        });
    }
};