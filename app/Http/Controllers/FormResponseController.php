<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Form_Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormResponseController extends Controller
{
    public function show(Form $form)
    {
        abort_unless($form->is_active, 404);
        $form->load(['fields' => fn($q) => $q->orderBy('sort_order'), 'event']);
        return view('forms.show', compact('form'));
    }

    public function store(Request $request, Form $form)
    {
        abort_unless($form->is_active, 404);
        $form->load(['fields' => fn($q) => $q->orderBy('sort_order')]);

        // Fix #7: checkbox fields send field_X[] arrays — use 'array' rule instead of 'string'
        $rules = [];
        foreach ($form->fields as $field) {
            $base = $field->required ? ['required'] : ['nullable'];

            $typeRule = match ($field->type) {
                'email'    => ['email', 'max:255'],
                'number'   => ['numeric'],
                'rating'   => ['integer', 'between:1,5'],
                'checkbox' => ['array'],          // HTML sends field_X[] arrays
                default    => ['string', 'max:2000'],
            };

            $rules["field_{$field->id}"] = array_merge($base, $typeRule);

            // Validate individual checkbox options against allowed values
            if ($field->type === 'checkbox' && $field->options) {
                $rules["field_{$field->id}.*"] = ['string', 'in:' . implode(',', $field->options)];
            }
        }

        $data = $request->validate($rules);

        $responses = [];
        foreach ($form->fields as $field) {
            $value = $data["field_{$field->id}"] ?? null;
            // Normalise checkbox arrays to JSON-serialisable form
            $responses[$field->id] = is_array($value) ? $value : $value;
        }

        $attendeeId = null;
        if (auth()->check()) {
            $attendee = $form->event->attendees()
                ->where('email', auth()->user()->email)
                ->first();
            $attendeeId = $attendee?->id;
        }

        Form_Response::create([
            'form_id'       => $form->id,
            'attendee_id'   => $attendeeId,
            'session_token' => $attendeeId ? null : Str::uuid(),
            'responses'     => $responses,
            'submitted_at'  => now(),
        ]);

        return redirect()->back()->with('success', 'Response submitted — thank you!');
    }
}
