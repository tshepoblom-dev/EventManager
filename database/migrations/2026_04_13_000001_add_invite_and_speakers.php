<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add invite token to attendees so they can be invited to create accounts
        Schema::table('attendees', function (Blueprint $table) {
            $table->string('invite_token', 64)->nullable()->unique()->after('source');
            $table->timestamp('invite_sent_at')->nullable()->after('invite_token');
        });

        // Speakers table: links a User (who may also be an Attendee) as a speaker profile
        Schema::create('speakers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('attendee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');                         // display name (copied from user/attendee or entered manually)
            $table->string('email')->nullable();
            $table->string('title')->nullable();            // e.g. "CEO at Acme"
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('twitter')->nullable();
            $table->timestamps();
        });

        // Replace session_speakers user_id FK with speaker_id FK
        // We keep user_id for backward compat but add speaker_id as the primary link
        Schema::table('session_speakers', function (Blueprint $table) {
            $table->foreignId('speaker_id')
                ->nullable()
                ->after('event_session_id')
                ->constrained('speakers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('session_speakers', function (Blueprint $table) {
            $table->dropForeign(['speaker_id']);
            $table->dropColumn('speaker_id');
        });

        Schema::dropIfExists('speakers');

        Schema::table('attendees', function (Blueprint $table) {
            $table->dropColumn(['invite_token', 'invite_sent_at']);
        });
    }
};
