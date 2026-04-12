<?php

namespace App\Services;

use App\Models\Attendee;
use App\Models\Event;
use App\Jobs\GenerateAttendeeQr;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CsvImportService
{
    private const CHUNK_SIZE = 50;

    private array $requiredColumns = ['first_name', 'last_name', 'email'];
    private array $optionalColumns = ['phone', 'company', 'job_title', 'ticket_type'];

    /**
     * Import attendees from an uploaded CSV file.
     * Fix #6:  Batch-inserts in chunks of 50 (was one INSERT per row).
     * Fix #26: Optional fields stored as null, not empty string.
     *
     * @return array{imported: int, skipped: int, errors: array}
     */
    public function import(UploadedFile $file, Event $event): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        $rawHeaders = fgetcsv($handle);
        if (! $rawHeaders) {
            fclose($handle);
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Empty or invalid CSV file.']];
        }

        $headers = array_map(fn($h) => strtolower(trim($h)), $rawHeaders);

        foreach ($this->requiredColumns as $required) {
            if (! in_array($required, $headers)) {
                fclose($handle);
                return ['imported' => 0, 'skipped' => 0, 'errors' => ["Missing required column: {$required}"]];
            }
        }

        // O(1) duplicate lookup
        $existingEmails = Attendee::where('event_id', $event->id)
            ->pluck('email')
            ->map(fn($e) => strtolower($e))
            ->flip();

        $imported  = 0;
        $skipped   = 0;
        $errors    = [];
        $row       = 1;
        $batch     = [];   // rows pending bulk insert
        $now       = now()->toDateTimeString();

        $flush = function () use (&$batch, &$imported, $event) {
            if (empty($batch)) {
                return;
            }

            // Bulk insert — single query per chunk
            DB::table('attendees')->insert($batch);
            $imported += count($batch);
            $batch = [];
        };

        while (($data = fgetcsv($handle)) !== false) {
            $row++;

            if (count($data) !== count($headers)) {
                $errors[] = "Row {$row}: column count mismatch — skipped.";
                $skipped++;
                continue;
            }

            $record = array_combine($headers, $data);
            $email  = strtolower(trim($record['email'] ?? ''));

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row {$row}: invalid email '{$email}' — skipped.";
                $skipped++;
                continue;
            }

            if (isset($existingEmails[$email])) {
                $skipped++;
                continue;
            }

            // Fix #26: nullable() helper — empty CSV fields become null, not ""
            $nullable = fn(string $key): ?string =>
                ($v = trim($record[$key] ?? '')) !== '' ? $v : null;

            $batch[] = [
                'event_id'    => $event->id,
                'first_name'  => trim($record['first_name']),
                'last_name'   => trim($record['last_name']),
                'email'       => $email,
                'phone'       => $nullable('phone'),
                'company'     => $nullable('company'),
                'job_title'   => $nullable('job_title'),
                'ticket_type' => $nullable('ticket_type') ?? 'general',
                'source'      => 'csv',
                'status'      => 'registered',
                'created_at'  => $now,
                'updated_at'  => $now,
            ];

            // Mark as seen to catch in-file duplicates
            $existingEmails[$email] = true;

            // Flush when chunk is full
            if (count($batch) >= self::CHUNK_SIZE) {
                $flush();
            }
        }

        fclose($handle);

        // Flush remaining rows
        $flush();

        // Queue QR generation in bulk AFTER all inserts succeed.
        // Fix #14: GenerateAttendeeQr implements ShouldBeUnique, so duplicates are de-duped.
        // Queue on dedicated 'qr' queue so QR jobs don't compete with broadcasts.
        if ($imported > 0) {
            $newAttendees = Attendee::where('event_id', $event->id)
                ->whereNull('qr_code')
                ->get();

            foreach ($newAttendees as $attendee) {
                GenerateAttendeeQr::dispatch($attendee)->onQueue('qr');
            }
        }

        return compact('imported', 'skipped', 'errors');
    }
}
