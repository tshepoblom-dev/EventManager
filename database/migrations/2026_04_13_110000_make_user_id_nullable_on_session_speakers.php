<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_speakers', function (Blueprint $table) {
            // user_id was added before Speaker profiles existed and assumed every
            // speaker had a user account. Now that speaker_id is the primary link,
            // user_id is optional (a speaker may not have a platform account).
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('session_speakers', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
