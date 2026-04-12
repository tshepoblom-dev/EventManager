<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix #16: Add indexes on all hot query paths.
 * Without these, every check-in scan, lead lookup, and feedback query
 * performs a full table scan — catastrophic at 1000+ live connections.
 */
return new class extends Migration
{
    public function up(): void
    {
        // attendees: most queries filter by event_id; email lookups for check-in search
        Schema::table('attendees', function (Blueprint $table) {
            $table->index('event_id',  'idx_attendees_event_id');
            $table->index('email',     'idx_attendees_email');
            $table->index('qr_code',   'idx_attendees_qr_code');
            $table->index('status',    'idx_attendees_status');
        });

        // check_ins: unique constraint prevents double check-in race condition (Fix #5)
        Schema::table('check_ins', function (Blueprint $table) {
            $table->index('event_id',    'idx_check_ins_event_id');
            $table->index('attendee_id', 'idx_check_ins_attendee_id');
            $table->index('checked_in_at', 'idx_check_ins_checked_in_at');
            // Unique per attendee — the DB-level guard for the race condition
            $table->unique('attendee_id', 'uq_check_ins_attendee_id');
        });

        // leads: filtered by event_id + pipeline_stage frequently (dashboard, kanban)
        Schema::table('leads', function (Blueprint $table) {
            $table->index('event_id',       'idx_leads_event_id');
            $table->index('sponsor_id',     'idx_leads_sponsor_id');
            $table->index(['event_id', 'pipeline_stage'], 'idx_leads_event_stage');
            $table->index(['event_id', 'interest_level'], 'idx_leads_event_interest');
        });

        // feedback: aggregated per session for ratings
        Schema::table('feedback', function (Blueprint $table) {
            $table->index('event_session_id', 'idx_feedback_session_id');
            $table->index('event_id',         'idx_feedback_event_id');
            $table->index('attendee_id',      'idx_feedback_attendee_id');
        });

        // event_sessions: filtered by event_id + time range for currentSession()
        Schema::table('event_sessions', function (Blueprint $table) {
            $table->index('event_id',      'idx_sessions_event_id');
            $table->index('is_highlighted','idx_sessions_highlighted');
            $table->index(['event_id', 'starts_at', 'ends_at'], 'idx_sessions_event_time');
        });

        // connections: both requester_id and receiver_id queried in OR conditions
        Schema::table('connections', function (Blueprint $table) {
            $table->index('event_id',     'idx_connections_event_id');
            $table->index('requester_id', 'idx_connections_requester');
            $table->index('receiver_id',  'idx_connections_receiver');
        });

        // form_responses: filtered by form_id for submissions list
        Schema::table('form__responses', function (Blueprint $table) {
            $table->index('form_id',     'idx_form_responses_form_id');
            $table->index('attendee_id', 'idx_form_responses_attendee_id');
        });

        // qr_codes: token lookups on every scan
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->index('attendee_id', 'idx_qr_codes_attendee_id');
        });
    }

    public function down(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->dropIndex('idx_attendees_event_id');
            $table->dropIndex('idx_attendees_email');
            $table->dropIndex('idx_attendees_qr_code');
            $table->dropIndex('idx_attendees_status');
        });

        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropIndex('idx_check_ins_event_id');
            $table->dropIndex('idx_check_ins_attendee_id');
            $table->dropIndex('idx_check_ins_checked_in_at');
            $table->dropUnique('uq_check_ins_attendee_id');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('idx_leads_event_id');
            $table->dropIndex('idx_leads_sponsor_id');
            $table->dropIndex('idx_leads_event_stage');
            $table->dropIndex('idx_leads_event_interest');
        });

        Schema::table('feedback', function (Blueprint $table) {
            $table->dropIndex('idx_feedback_session_id');
            $table->dropIndex('idx_feedback_event_id');
            $table->dropIndex('idx_feedback_attendee_id');
        });

        Schema::table('event_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_sessions_event_id');
            $table->dropIndex('idx_sessions_highlighted');
            $table->dropIndex('idx_sessions_event_time');
        });

        Schema::table('connections', function (Blueprint $table) {
            $table->dropIndex('idx_connections_event_id');
            $table->dropIndex('idx_connections_requester');
            $table->dropIndex('idx_connections_receiver');
        });

        Schema::table('form__responses', function (Blueprint $table) {
            $table->dropIndex('idx_form_responses_form_id');
            $table->dropIndex('idx_form_responses_attendee_id');
        });

        Schema::table('qr_codes', function (Blueprint $table) {
            $table->dropIndex('idx_qr_codes_attendee_id');
        });
    }
};
