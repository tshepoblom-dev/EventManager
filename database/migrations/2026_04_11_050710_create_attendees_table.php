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
        Schema::create('attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->string('ticket_type')->default('general'); // general, vip, speaker, sponsor
            $table->string('status')->default('registered'); // registered, confirmed, cancelled
            $table->string('qr_code')->nullable()->unique();
            $table->string('qr_image_path')->nullable();
            $table->boolean('qr_emailed')->default(false);
            $table->string('source')->default('manual'); // manual, csv, online
            $table->timestamps();
            $table->unique(['event_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendees');
    }
};
