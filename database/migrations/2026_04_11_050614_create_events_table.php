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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('venue')->nullable();
            $table->date('event_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status')->default('draft'); // draft, published, live, closed
            $table->string('logo')->nullable();
            $table->string('primary_color')->default('#1a56db');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
