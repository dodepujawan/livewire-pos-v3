@props([
    'label' => '',
    'name' => '',
])

<div class="mb-5">

    @if($name)
        <label
            for="{{ $name }}"
            class="mb-2 block text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif

    <select
        @if($name)
            id="{{ $name }}"
            name="{{ $name }}"
        @endif

        {{ $attributes->merge([
            'class' =>
                'w-full rounded-lg border border-gray-300 px-3 py-2
                focus:border-blue-500 focus:ring focus:ring-blue-200'
        ]) }}
    >

        {{ $slot }}

    </select>

    @if($name)
        @error($name)
            <p class="mt-1 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    @endif

</div>
