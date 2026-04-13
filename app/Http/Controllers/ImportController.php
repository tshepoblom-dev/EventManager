<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Form_field;
use App\Models\Form_Response;
use App\Models\Attendee;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function import()
    {
        // ─────────────────────────────
        // CONFIG
        // ─────────────────────────────
        $formId = 1;
        $eventId = 1; // Assuming event ID 1 exists
        $path   = storage_path('app/import.csv');
        $dryRun = false; // 🔁 set true to test without saving

        DB::beginTransaction();
        
        try {
            // ─────────────────────────────
            // STEP 1: ENSURE FORM EXISTS (without mass assigning ID)
            // ─────────────────────────────
            $form = Form::find($formId);
            
            if (!$form) {
                // Create new form without specifying ID (let DB auto-increment)
                $form = Form::create([
                    'event_id' => $eventId,
                    'title' => 'Business Registration & Support Form',
                    'description' => 'Imported from CSV - Business survey data',
                    'type' => 'registration',
                    'is_active' => true,
                    'allow_anonymous' => false,
                ]);
                Log::info('Form created', ['form_id' => $form->id, 'title' => $form->title]);
            } else {
                Log::info('Form already exists', ['form_id' => $form->id, 'title' => $form->title]);
            }

            // ─────────────────────────────
            // STEP 2: DEFINE COMPLETE FORM FIELDS WITH OPTIONS
            // ─────────────────────────────
            $fieldsDefinition = $this->getFieldsDefinition();
            
            $createdFields = 0;
            $existingFields = 0;

            foreach ($fieldsDefinition as $fieldData) {
                // Check if field already exists by label
                $existingField = Form_field::where('form_id', $form->id)
                    ->where('label', $fieldData['label'])
                    ->first();
                
                if (!$existingField) {
                    // Create new field only if it doesn't exist
                    Form_field::create(array_merge($fieldData, ['form_id' => $form->id]));
                    $createdFields++;
                    Log::info('Field created', ['label' => $fieldData['label']]);
                } else {
                    $existingFields++;
                    Log::info('Field already exists, skipped', ['label' => $fieldData['label']]);
                }
            }

            Log::info('Form fields sync completed', [
                'created' => $createdFields,
                'existing' => $existingFields,
                'total' => count($fieldsDefinition)
            ]);

            // Reload form with fields
            $form->load('fields');
            $fieldMap = $form->fields->keyBy('label');

            // ─────────────────────────────
            // STEP 3: IMPORT CSV DATA
            // ─────────────────────────────
            
            // Check if file exists
            if (!file_exists($path)) {
                throw new \Exception("CSV file not found at: {$path}");
            }

            $report = [
                'total_rows' => 0,
                'imported'   => 0,
                'skipped'    => 0,
                'duplicates' => 0,
                'errors'     => 0,
                'attendees_created' => 0,
                'attendees_updated' => 0,
                'issues'     => [],
            ];

            // Normalizer function
            $normalize = function ($str) {
                if (!$str) return '';
                return strtolower(trim(preg_replace('/\s+/', ' ', str_replace(["\n","\r"], ' ', $str))));
            };

            // Load CSV
            $file = fopen($path, 'r');
            $headers = fgetcsv($file, 0, ',', '"', '\\');
            
            if (!$headers) {
                throw new \Exception('Could not read CSV headers');
            }
            
            $normalizedHeaders = array_map($normalize, $headers);
            
            // Read all rows
            $rows = [];
            while (($row = fgetcsv($file, 0, ',', '"', '\\')) !== false) {
                if (count($row) < count($headers)) {
                    $row = array_pad($row, count($headers), '');
                }
                $rows[] = $row;
            }
            fclose($file);

            Log::info('CSV loaded', ['rows' => count($rows), 'headers' => count($headers)]);

            // Track processed emails to avoid duplicate attendee creation within same import
            $processedEmails = [];

            // Import loop
            foreach ($rows as $index => $row) {
                $report['total_rows']++;
                $rowNumber = $index + 2;

                try {
                    // First, extract attendee data from the row
                    $attendeeData = $this->extractAttendeeData($normalizedHeaders, $row, $rowNumber);
                    
                    // Skip if no email (required for attendee)
                    if (empty($attendeeData['email'])) {
                        $report['skipped']++;
                        $report['issues'][] = "Row {$rowNumber}: Skipped - No email address found";
                        continue;
                    }

                    // Check for duplicate email within this import session
                    if (in_array($attendeeData['email'], $processedEmails)) {
                        $report['duplicates']++;
                        $report['issues'][] = "Row {$rowNumber}: Duplicate email ({$attendeeData['email']}) within import";
                        continue;
                    }

                    // Check for existing attendee in database
                    $existingAttendee = Attendee::where('email', $attendeeData['email'])
                        ->where('event_id', $eventId)
                        ->first();

                    if ($existingAttendee && !$dryRun) {
                        // Update existing attendee
                        $existingAttendee->update($attendeeData);
                        $report['attendees_updated']++;
                        $attendee = $existingAttendee;
                        Log::info("Row {$rowNumber}: Updated existing attendee", ['email' => $attendeeData['email']]);
                    } elseif (!$dryRun) {
                        // Create new attendee
                        $attendee = Attendee::create(array_merge($attendeeData, [
                            'event_id' => $eventId,
                            'status' => 'registered',
                            'source' => 'csv_import',
                        ]));
                        $report['attendees_created']++;
                        Log::info("Row {$rowNumber}: Created new attendee", ['email' => $attendeeData['email']]);
                    } else {
                        // Dry run - just pretend
                        $attendee = null;
                        Log::info("Row {$rowNumber}: Dry run - would create/update attendee", ['email' => $attendeeData['email']]);
                    }

                    $processedEmails[] = $attendeeData['email'];

                    // Now process form responses
                    $responses = [];
                    $hasAnyData = false;

                    foreach ($normalizedHeaders as $i => $headerKey) {
                        // Skip timestamp and attendee-mapped columns
                        if (str_contains($headerKey, 'timestamp')) {
                            continue;
                        }

                        $value = isset($row[$i]) ? trim($row[$i]) : '';
                        
                        if ($value === '' || $value === null) {
                            continue;
                        }
                        
                        // Skip fields that are mapped to attendee (already handled)
                        if ($this->isAttendeeField($headerKey)) {
                            continue;
                        }
                        
                        $hasAnyData = true;

                        // Resolve form label
                        $formLabel = $this->resolveFieldLabel($headerKey);
                        if (!$formLabel) {
                            $report['issues'][] = "Row {$rowNumber}: No match for header '{$headerKey}'";
                            continue;
                        }

                        $field = $fieldMap[$formLabel] ?? null;
                        
                        if (!$field) {
                            $report['issues'][] = "Row {$rowNumber}: No field found for '{$formLabel}'";
                            continue;
                        }

                        // Clean phone number (but phone already mapped to attendee, so this is for form response)
                        if ($field->label === 'Cellphone Number') {
                            $value = preg_replace('/\D+/', '', $value);
                        }

                        // Handle checkbox/multi-select fields
                        if ($field->type === 'checkbox' || 
                            in_array($field->label, ['Interested Support', 'Brand Presence', 'Opportunities Interested In'])) {
                            $parts = array_map('trim', explode(';', $value));
                            $value = array_values(array_filter($parts, function($part) {
                                return $part !== '' && $part !== 'None of the above';
                            }));
                            
                            if (empty($value)) {
                                continue;
                            }
                        }

                        // Normalize Yes/No/Maybe fields
                        if (in_array($field->type, ['dropdown', 'radio'])) {
                            $lowerValue = strtolower($value);
                            if (in_array($lowerValue, ['yes', 'no', 'maybe'])) {
                                $value = ucfirst($lowerValue);
                            }
                        }

                        $responses[$field->id] = $value;
                    }

                    // Only create form response if there's data and not dry run
                    if (!$dryRun && $hasAnyData && !empty($responses) && $attendee) {
                        // Check for existing form response (duplicate by attendee and form)
                        $existingResponse = Form_Response::where('form_id', $form->id)
                            ->where('attendee_id', $attendee->id)
                            ->first();

                        if (!$existingResponse) {
                            Form_Response::create([
                                'form_id'       => $form->id,
                                'attendee_id'   => $attendee->id,
                                'session_token' => Str::uuid(),
                                'responses'     => $responses,
                                'submitted_at'  => now(),
                            ]);
                            $report['imported']++;
                            Log::info("Row {$rowNumber}: Created form response", ['attendee_id' => $attendee->id]);
                        } else {
                            $report['skipped']++;
                            $report['issues'][] = "Row {$rowNumber}: Form response already exists for attendee";
                        }
                    } elseif (!$dryRun) {
                        $report['skipped']++;
                        $report['issues'][] = "Row {$rowNumber}: No form data to save";
                    } else {
                        $report['imported']++; // Count as imported in dry run
                    }

                } catch (\Throwable $e) {
                    $report['errors']++;
                    $report['issues'][] = "Row {$rowNumber}: " . $e->getMessage();
                    Log::error('CSV Import Error', [
                        'row' => $rowNumber,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            DB::commit();

            // Final report
            Log::info('CSV Import Completed', $report);

            return response()->json([
                'status' => 'completed',
                'message' => "Import completed! Form fields: {$createdFields} created, {$existingFields} existing | Imported: {$report['imported']}, Skipped: {$report['skipped']}, Duplicates: {$report['duplicates']}, Errors: {$report['errors']} | Attendees - Created: {$report['attendees_created']}, Updated: {$report['attendees_updated']}",
                'report' => $report,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Import failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Get complete field definitions with options for dropdowns and checkboxes
     */
    private function getFieldsDefinition()
    {
        return [
            // Basic Info
            ['label' => 'Name', 'type' => 'text', 'required' => false, 'sort_order' => 1, 'options' => null],
            ['label' => 'Email', 'type' => 'email', 'required' => false, 'sort_order' => 2, 'options' => null],
            ['label' => 'Cellphone Number', 'type' => 'tel', 'required' => false, 'sort_order' => 3, 'options' => null],
            ['label' => 'Business Name', 'type' => 'text', 'required' => false, 'sort_order' => 4, 'options' => null],
            
            // Business Details
            ['label' => 'Industry/Sector', 'type' => 'dropdown', 'required' => false, 'sort_order' => 5, 'options' => [
                'Creative/Digital', 'Food & Beverage', 'Retail', 'Services', 'Manufacturing', 
                'Construction', 'Agriculture', 'Technology', 'Health & Beauty', 'Education',
                'Transportation', 'Real Estate', 'Finance', 'Entertainment', 'Other'
            ]],
            ['label' => 'Stage of Business', 'type' => 'dropdown', 'required' => false, 'sort_order' => 6, 'options' => [
                'Idea Stage', 'Less than 1 year', '1 - 3 years', '3+ years'
            ]],
            ['label' => 'Biggest growth challenge', 'type' => 'dropdown', 'required' => false, 'sort_order' => 7, 'options' => [
                'Access to funding', 'Marketing & Branding', 'Compliance (CIPC/SARS/Municipality)',
                'Systems & Processes', 'Managing cash flow', 'Bookkeeping', 'Getting more customers',
                'Scaling operations', 'Other'
            ]],
            ['label' => 'Employee Count', 'type' => 'dropdown', 'required' => false, 'sort_order' => 8, 'options' => [
                'Only me', '1 - 5', '5 - 10', '10 - 20', 'More than 20'
            ]],
            ['label' => 'Current Operations', 'type' => 'dropdown', 'required' => false, 'sort_order' => 9, 'options' => [
                'Mostly manual (paper / WhatsApp)', 'Excel-based', 'Using multiple digital systems',
                'Using accounting software', 'Fully automated', 'none of the above'
            ]],
            
            // Support & Brand
            ['label' => 'Interested Support', 'type' => 'checkbox', 'required' => false, 'sort_order' => 10, 'options' => [
                'Business automation', 'Custom app or system development', 'Website development',
                'Process improvement', 'AI & digital tools', 'Not at this stage'
            ]],
            ['label' => 'Brand Presence', 'type' => 'checkbox', 'required' => false, 'sort_order' => 11, 'options' => [
                'Professional logo & brand identity', 'Active social media presence', 'Website', 'None of the above'
            ]],
            ['label' => 'Business Address', 'type' => 'textarea', 'required' => false, 'sort_order' => 12, 'options' => null],
            ['label' => 'Brand Visibility Support', 'type' => 'dropdown', 'required' => false, 'sort_order' => 13, 'options' => [
                'Yes', 'No', 'Maybe'
            ]],
            
            // Financial
            ['label' => 'Formal Financial Statements', 'type' => 'dropdown', 'required' => false, 'sort_order' => 14, 'options' => [
                'Yes', 'No', 'Only basic records'
            ]],
            ['label' => 'Dedicated Business Bank Account', 'type' => 'dropdown', 'required' => false, 'sort_order' => 15, 'options' => [
                'Yes', 'No'
            ]],
            ['label' => 'Monthly Turnover', 'type' => 'dropdown', 'required' => false, 'sort_order' => 16, 'options' => [
                'R0 - R5,000', 'R5,000 - R10,000', 'R10,000 - R30,000', 
                'R30,000 - R50,000', 'R50,000 - R100,000', 'R100,000+'
            ]],
            ['label' => 'Compliance Status', 'type' => 'dropdown', 'required' => false, 'sort_order' => 17, 'options' => [
                'Fully compliant (CIPC, SARS, COIDA, UIF all in place)',
                'Partially compliant', 'Registered but not compliant', 'Not registered', 'Not sure'
            ]],
            
            // Experience & Opportunities
            ['label' => 'Have you ever pitched before?', 'type' => 'dropdown', 'required' => false, 'sort_order' => 18, 'options' => [
                'Yes', 'No'
            ]],
            ['label' => 'Opportunities Interested In', 'type' => 'checkbox', 'required' => false, 'sort_order' => 19, 'options' => [
                'Access to funding', 'Business incubation', 'Pitch competitions',
                'Corporate supply opportunities', 'Hosting or exhibiting at large events',
                'Professional event planning & decor services', 'Ongoing mentorship'
            ]],
            
            // Exhibition
            ['label' => 'Exhibit Interest', 'type' => 'dropdown', 'required' => false, 'sort_order' => 20, 'options' => [
                'Yes', 'No'
            ]],
            ['label' => 'Exhibition Details', 'type' => 'textarea', 'required' => false, 'sort_order' => 21, 'options' => null],
            
            // Consent
            ['label' => 'Permission to Contact', 'type' => 'dropdown', 'required' => false, 'sort_order' => 22, 'options' => [
                'Yes', 'No'
            ]],
        ];
    }

    /**
     * Extract attendee data from CSV row
     */
    private function extractAttendeeData($normalizedHeaders, $row, $rowNumber)
    {
        $data = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'company' => '',
            'job_title' => '',
            'ticket_type' => 'General Admission', // Default ticket type
        ];

        foreach ($normalizedHeaders as $i => $headerKey) {
            $value = isset($row[$i]) ? trim($row[$i]) : '';
            if ($value === '') continue;

            // Map CSV fields to attendee fields
            if (str_contains($headerKey, 'name') && !str_contains($headerKey, 'business')) {
                // Handle full name - split into first and last name
                $nameParts = explode(' ', $value, 2);
                $data['first_name'] = $nameParts[0];
                $data['last_name'] = isset($nameParts[1]) ? $nameParts[1] : '_';
            }
            elseif (str_contains($headerKey, 'email')) {
                $data['email'] = strtolower($value);
            }
            elseif (str_contains($headerKey, 'cellphone')) {
                // Clean phone number
                $data['phone'] = preg_replace('/\D+/', '', $value);
            }
            elseif (str_contains($headerKey, 'business name')) {
                $data['company'] = $value;
            }
            elseif (str_contains($headerKey, 'job') || str_contains($headerKey, 'position')) {
                $data['job_title'] = $value;
            }
        }

        // Validate email
        if (empty($data['email'])) {
            Log::warning("Row {$rowNumber}: No email found for attendee");
        }

        // Ensure last_name has a value (use '_' if empty)
        if (empty($data['last_name'])) {
            $data['last_name'] = '_';
        }

        return $data;
    }

    /**
     * Check if a CSV header maps to an attendee field
     */
    private function isAttendeeField($headerKey)
    {
        $attendeeFields = ['name', 'email', 'cellphone', 'business name', 'job'];
        
        foreach ($attendeeFields as $field) {
            if (str_contains($headerKey, $field)) {
                return true;
            }
        }
        return false;
    }

    function resolveFieldLabel($headerKey)
    {
        // Exact matches for normalized headers
        $exactMatches = [
            'name' => 'Name',
            'email' => 'Email',
            'cellphone number' => 'Cellphone Number',
            'business name' => 'Business Name',
            'industry/sector' => 'Industry/Sector',
            'stage of business' => 'Stage of Business',
            'what is your biggest growth challenge right now?' => 'Biggest growth challenge',
            'how many people do you employ?' => 'Employee Count',
            'which best describes your current operations?' => 'Current Operations',
            'would you be interested in support with any of the following?' => 'Interested Support',
            'does your business currently have' => 'Brand Presence',
            'where does your business operate from? (address)' => 'Business Address',
            'would you like support improving your brand visibility?' => 'Brand Visibility Support',
            'do you prepare formal financial statements?' => 'Formal Financial Statements',
            'do you have a dedicated business bank account?' => 'Dedicated Business Bank Account',
            'what is your monthly turnover?' => 'Monthly Turnover',
            'what is your current compliance status?' => 'Compliance Status',
            'have you ever pitched your business before ?' => 'Have you ever pitched before?',
            'which opportunities would you like access to?' => 'Opportunities Interested In',
            'would you like to exhibit some of your products at the event?' => 'Exhibit Interest',
            'may our partner organisations contact you regarding relevant business support opportunities?' => 'Permission to Contact',
        ];
        
        // Check exact matches
        foreach ($exactMatches as $key => $label) {
            if ($headerKey === $key) {
                return $label;
            }
        }
        
        // Special handling for exhibition details (multiline)
        if (str_contains($headerKey, 'share some details about your display') || 
            str_contains($headerKey, 'what products are you going to display')) {
            return 'Exhibition Details';
        }
        
        // Fallback to partial matches
        if (str_contains($headerKey, 'name') && !str_contains($headerKey, 'business')) return 'Name';
        if (str_contains($headerKey, 'email')) return 'Email';
        if (str_contains($headerKey, 'cellphone')) return 'Cellphone Number';
        if (str_contains($headerKey, 'business name')) return 'Business Name';
        if (str_contains($headerKey, 'industry')) return 'Industry/Sector';
        if (str_contains($headerKey, 'stage of business')) return 'Stage of Business';
        if (str_contains($headerKey, 'biggest growth challenge')) return 'Biggest growth challenge';
        if (str_contains($headerKey, 'how many people')) return 'Employee Count';
        if (str_contains($headerKey, 'current operations')) return 'Current Operations';
        if (str_contains($headerKey, 'interested in support')) return 'Interested Support';
        if (str_contains($headerKey, 'business currently have')) return 'Brand Presence';
        if (str_contains($headerKey, 'operate from')) return 'Business Address';
        if (str_contains($headerKey, 'brand visibility')) return 'Brand Visibility Support';
        if (str_contains($headerKey, 'formal financial statements')) return 'Formal Financial Statements';
        if (str_contains($headerKey, 'business bank account')) return 'Dedicated Business Bank Account';
        if (str_contains($headerKey, 'monthly turnover')) return 'Monthly Turnover';
        if (str_contains($headerKey, 'compliance status')) return 'Compliance Status';
        if (str_contains($headerKey, 'pitched your business')) return 'Have you ever pitched before?';
        if (str_contains($headerKey, 'opportunities would you like')) return 'Opportunities Interested In';
        if (str_contains($headerKey, 'exhibit some of your products')) return 'Exhibit Interest';
        if (str_contains($headerKey, 'partner organisations contact you')) return 'Permission to Contact';

        return null;
    }
}