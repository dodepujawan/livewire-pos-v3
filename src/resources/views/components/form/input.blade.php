@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
])

<div class="mb-5">

    <label for="{{ $name }}" class="mb-2 block text-sm font-medium text-gray-700">

        {{ $label }}

    </label>

    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
        {{ $attributes->merge([
            'class' => 'w-full rounded-lg border border-gray-300 px-3 py-2
                         focus:border-blue-500 focus:ring focus:ring-blue-200',
        ]) }}>

    @if ($name)
        @error($name)
            <p class="mt-1 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    @endif

</div>
