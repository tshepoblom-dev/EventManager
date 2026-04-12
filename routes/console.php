<?php

use Illuminate\Support\Facades\Schedule;

// Broadcast the live session every minute during event hours
Schedule::command('sessions:highlight')->everyMinute();

// Send reminder emails at 08:00 and thank-you emails daily
Schedule::command('events:reminders')->dailyAt('08:00');
