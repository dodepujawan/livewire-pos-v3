<button
    {{ $attributes->merge([
        'type'=>'button',
        'class'=>'inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700'
    ]) }}
>

    {{ $slot }}

</button>
