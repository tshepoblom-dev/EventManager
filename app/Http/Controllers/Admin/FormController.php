<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Form;
use App\Models\Form_field;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function index(Event $event)
    {
        $forms = $event->forms()->withCount('responses')->get();
        return view('admin.forms.index', compact('event', 'forms'));
    }

    public function create(Event $event)
    {
        return view('admin.forms.create', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'type'            => 'required|in:feedback,lead_capture,survey,sponsor_interest',
            'is_active'       => 'boolean',
            'allow_anonymous' => 'boolean',
        ]);

        $form = $event->forms()->create([
            ...$validated,
            'is_active'       => $request->boolean('is_active', true),
            'allow_anonymous' => $request->boolean('allow_anonymous'),
        ]);

        return redirect()->route('admin.events.forms.edit', [$event, $form])
            ->with('success', 'Form created. Now add your fields below.');
    }

    public function edit(Event $event, Form $form)
    {
        $form->load(['fields' => fn($q) => $q->orderBy('sort_order')]);
        return view('admin.forms.edit', compact('event', 'form'));
    }

    public function update(Request $request, Event $event, Form $form)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'type'            => 'required|in:feedback,lead_capture,survey,sponsor_interest',
        ]);

        $form->update([
            ...$validated,
            'is_active'       => $request->boolean('is_active'),
            'allow_anonymous' => $request->boolean('allow_anonymous'),
        ]);

        // Fix #27: validate the JSON payload before passing to syncFields.
        // json_decode returns null on malformed JSON — previously this caused a
        // TypeError in syncFields(null) which was unhandled and returned a 500.
        if ($request->filled('fields')) {
            $raw    = $request->input('fields');
            $fields = json_decode($raw, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()
                    ->withInput()
                    ->withErrors(['fields' => 'Invalid field data — please try saving again.']);
            }

            if (! is_array($fields)) {
                return back()
                    ->withInput()
                    ->withErrors(['fields' => 'Field data must be an array.']);
            }

            $this->syncFields($form, $fields);
        }

        return redirect()->route('admin.events.forms.edit', [$event, $form])
            ->with('success', 'Form saved.');
    }

    public function show(Event $event, Form $form)
    {
        $form->load(['fields' => fn($q) => $q->orderBy('sort_order'), 'responses.attendee']);
        return view('admin.forms.show', compact('event', 'form'));
    }

    public function destroy(Event $event, Form $form)
    {
        $form->delete();
        return redirect()->route('admin.events.forms.index', $event)
            ->with('success', 'Form deleted.');
    }

    // ── Field management (AJAX) ─────────────────────────────────────────

    public function storeField(Request $request, Event $event, Form $form)
    {
        $validated = $request->validate([
            'label'       => 'required|string|max:255',
            'type'        => 'required|in:text,number,dropdown,checkbox,rating,textarea,email,phone',
            'required'    => 'boolean',
            'placeholder' => 'nullable|string|max:255',
            'options'     => 'nullable|array',
        ]);

        $maxOrder = $form->fields()->max('sort_order') ?? 0;

        $field = $form->fields()->create([
            ...$validated,
            'required'   => $request->boolean('required'),
            'sort_order' => $maxOrder + 1,
            'options'    => $validated['options'] ?? null,
        ]);

        return response()->json(['field' => $field]);
    }

    public function destroyField(Event $event, Form $form, Form_field $field)
    {
        $field->delete();
        return response()->json(['ok' => true]);
    }

    public function reorderFields(Request $request, Event $event, Form $form)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer']);

        foreach ($request->order as $index => $fieldId) {
            $form->fields()->where('id', $fieldId)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['ok' => true]);
    }

    // ── Private helpers ─────────────────────────────────────────────────

    private function syncFields(Form $form, array $fields): void
    {
        $existingIds = $form->fields()->pluck('id')->all();
        $incomingIds = array_filter(array_column($fields, 'id'));

        $form->fields()->whereNotIn('id', $incomingIds)->delete();

        foreach ($fields as $index => $fieldData) {
            $data = [
                'form_id'     => $form->id,
                'label'       => $fieldData['label'] ?? '',
                'type'        => $fieldData['type']  ?? 'text',
                'required'    => (bool) ($fieldData['required'] ?? false),
                'placeholder' => $fieldData['placeholder'] ?? null,
                'options'     => $fieldData['options'] ?? null,
                'sort_order'  => $index + 1,
            ];

            if (!empty($fieldData['id']) && in_array($fieldData['id'], $existingIds)) {
                Form_field::where('id', $fieldData['id'])->update($data);
            } else {
                Form_field::create($data);
            }
        }
    }
}
