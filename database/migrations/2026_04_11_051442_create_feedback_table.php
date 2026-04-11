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
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('attendee_id')->nullable()->constrained()->nullOnDelete();
            $table->tinyInteger('rating')->nullable(); // 1-5
            $table->text('comment')->nullable();
            $table->string('type')->default('session'); // session, event, speaker
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
