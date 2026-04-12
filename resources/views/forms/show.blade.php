<!DOCTYPE html>
<html lang="en" class="bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $form->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen py-10">
<div class="max-w-lg mx-auto px-4">

    {{-- Header --}}
    <div class="mb-6">
        <p class="text-xs text-gray-400 mb-1">{{ $form->event->name }}</p>
        <h1 class="text-xl font-bold text-gray-900">{{ $form->title }}</h1>
        @if($form->description)
        <p class="text-sm text-gray-600 mt-2">{{ $form->description }}</p>
        @endif
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <form method="POST" action="{{ route('forms.store', $form) }}" class="px-6 py-6 space-y-5">
            @csrf

            @foreach($form->fields as $field)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    {{ $field->label }}
                    @if($field->required)<span class="text-red-500">*</span>@endif
                </label>

                @switch($field->type)

                @case('text')
                @case('email')
                @case('phone')
                    <input type="{{ $field->type === 'phone' ? 'tel' : $field->type }}"
                           name="field_{{ $field->id }}"
                           value="{{ old('field_'.$field->id) }}"
                           placeholder="{{ $field->placeholder }}"
                           {{ $field->required ? 'required' : '' }}
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('field_'.$field->id) border-red-400 @enderror">
                    @break

                @case('number')
                    <input type="number" name="field_{{ $field->id }}"
                           value="{{ old('field_'.$field->id) }}"
                           placeholder="{{ $field->placeholder }}"
                           {{ $field->required ? 'required' : '' }}
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('field_'.$field->id) border-red-400 @enderror">
                    @break

                @case('textarea')
                    <textarea name="field_{{ $field->id }}" rows="4"
                              placeholder="{{ $field->placeholder }}"
                              {{ $field->required ? 'required' : '' }}
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none @error('field_'.$field->id) border-red-400 @enderror">{{ old('field_'.$field->id) }}</textarea>
                    @break

                @case('dropdown')
                    <select name="field_{{ $field->id }}" {{ $field->required ? 'required' : '' }}
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— Select —</option>
                        @foreach($field->options ?? [] as $opt)
                        <option value="{{ $opt }}" {{ old('field_'.$field->id)===$opt?'selected':'' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                    @break

                @case('checkbox')
                    <div class="space-y-2">
                        @foreach($field->options ?? [] as $opt)
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                            <input type="checkbox" name="field_{{ $field->id }}[]" value="{{ $opt }}"
                                   {{ in_array($opt, (array)(old('field_'.$field->id) ?? [])) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                            {{ $opt }}
                        </label>
                        @endforeach
                    </div>
                    @break

                @case('rating')
                    <div class="flex items-center gap-1" x-data="{ val: {{ old('field_'.$field->id, 0) }} }">
                        <input type="hidden" name="field_{{ $field->id }}" :value="val">
                        @for($i = 1; $i <= 5; $i++)
                        <button type="button" @click="val = {{ $i }}"
                                :class="val >= {{ $i }} ? 'text-amber-400' : 'text-gray-200'"
                                class="text-3xl leading-none transition-colors hover:text-amber-300 focus:outline-none">★</button>
                        @endfor
                        <span x-show="val" x-text="val + '/5'" class="text-sm text-gray-500 ml-2"></span>
                    </div>
                    @break

                @endswitch

                @error('field_'.$field->id)
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            @endforeach

            <div class="pt-2">
                <button type="submit"
                        class="w-full px-5 py-3 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
